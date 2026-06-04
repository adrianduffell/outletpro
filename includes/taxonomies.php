<?php
/**
 * Taxonomy-related functions.
 *
 * @package WC_Outlet
 */

namespace WC_Outlet;

defined( 'ABSPATH' ) || exit;

/**
 * Non-public taxonomy used to represent the outlet status of products.
 *
 * Used with a canonical term for internal flagging of products belonging
 * in the outlet.
 *
 * @internal
 */
const OUTLET_STATUS_TAXONOMY = 'wc_outlet_status';

/**
 * Canonical term for products belonging in the outlet.
 *
 * @internal
 */
const OUTLET_STATUS_CANONICAL_TERM = 'outlet';

/**
 * Helper to initialize taxonomies.
 *
 * @internal
 */
function init_taxonomies(): void {
	register_outlet_status_taxonomy();
}

/**
 * Helper to report diagnostic info on taxonomies.
 *
 * @internal
 * @return array<string, array{0: string, 1: int|string}>
 */
function report_taxonomies(): array {
	$taxonomy_exists      = taxonomy_exists( OUTLET_STATUS_TAXONOMY );
	$canonical_term       = $taxonomy_exists ? get_term_by( 'name', OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY ) : null;
	$outlet_product_count = $taxonomy_exists ? count_outlet() : null;

	return array(
		'outlet-taxonomy-registered' => array(
			__( 'Outlet status taxonomy registered', 'wc-outlet' ),
			$taxonomy_exists ? __( 'Yes', 'wc-outlet' ) : __( 'No', 'wc-outlet' ),
		),
		'outlet-canonical-term-id'   => array(
			__( 'Canonical term ID', 'wc-outlet' ),
			$canonical_term instanceof \WP_Term ? $canonical_term->term_id : __( 'Not found', 'wc-outlet' ),
		),
		'outlet-product-count'       => array(
			__( 'Total products in outlet', 'wc-outlet' ),
			$outlet_product_count ?? __( 'Unknown', 'wc-outlet' ),
		),
	);
}

/**
 * Register the outlet status taxonomy.
 *
 * @internal
 */
function register_outlet_status_taxonomy(): void {
	$args = array(
		'label'        => __( 'Outlet Status', 'wc-outlet' ),
		'public'       => false,
		'show_ui'      => false,
		'show_in_rest' => false,
		'hierarchical' => false,
		'query_var'    => false,
		'rewrite'      => false,
		'capabilities' => array(
			'assign_terms' => 'edit_products',
			'manage_terms' => 'manage_product_terms',
			'edit_terms'   => 'manage_product_terms',
			'delete_terms' => 'manage_product_terms',
		),
		'meta_box_cb'  => false,
	);

	register_taxonomy( OUTLET_STATUS_TAXONOMY, 'product', $args );
}

/**
 * Seed the outlet status taxonomy with the canonical term.
 *
 * @internal
 * @throws \RuntimeException If the term seeding fails.
 */
function seed_outlet_status_taxonomy(): void {
	if ( term_exists( OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY ) ) {
		return;
	}

	$result = wp_insert_term( OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY );

	if ( is_wp_error( $result ) ) {
		throw new \RuntimeException( 'Failed to seed outlet status taxonomy.' );
	}
}

/**
 * Check if a product is in the outlet.
 *
 * @param \WC_Product $product The product to check.
 * @throws \RuntimeException If the outlet status taxonomy does not exist.
 * @throws \RuntimeException If a variation's parent product cannot be found.
 * @since 1.0.0
 */
function is_outlet( \WC_Product $product ): bool {
	if ( ! taxonomy_exists( OUTLET_STATUS_TAXONOMY ) ) {
		throw new \RuntimeException( 'Outlet status taxonomy does not exist.' );
	}

	// Handle variations by checking the parent product.
	if ( $product->is_type( 'variation' ) ) {
		$parent = wc_get_product( $product->get_parent_id() );
		if ( ! $parent ) {
			throw new \RuntimeException( 'Parent product for variation could not be found.' );
		}
		return is_outlet( $parent );
	}

	return has_term( OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY, $product->get_id() );
}

/**
 * Add a product to the outlet.
 *
 * @param \WC_Product $product Product to update.
 * @throws \RuntimeException If the store’s outlet status taxonomy does not exist or the term assignment fails.
 * @since 1.0.0
 */
function add_to_outlet( \WC_Product $product ): void {
	if ( ! taxonomy_exists( OUTLET_STATUS_TAXONOMY ) ) {
		throw new \RuntimeException( 'Outlet status taxonomy does not exist.' );
	}

	$result = wp_set_object_terms( $product->get_id(), OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY );

	if ( is_wp_error( $result ) ) {
		throw new \RuntimeException( 'Failed to assign outlet status term to product.' );
	}
}

/**
 * Count the number of published outlet products.
 *
 * @throws \RuntimeException If the outlet status taxonomy does not exist.
 * @since 1.0.0
 */
function count_outlet(): int {
	if ( ! taxonomy_exists( OUTLET_STATUS_TAXONOMY ) ) {
		throw new \RuntimeException( 'Outlet status taxonomy does not exist.' );
	}

	$canonical_term = get_term_by( 'name', OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY );

	if ( ! $canonical_term ) {
		return 0;
	}

	$query = new \WP_Query(
		array(
			'post_type'              => 'product',
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => OUTLET_STATUS_TAXONOMY,
					'field'    => 'term_id',
					'terms'    => $canonical_term->term_id,
				),
			),
		)
	);

	return $query->found_posts;
}

/**
 * Check if the store’s outlet is empty.
 *
 * More performant than count_outlet() because it uses no_found_rows to skip the SQL row count.
 *
 * @throws \RuntimeException If the store’s outlet status taxonomy does not exist.
 * @since 1.0.0
 */
function outlet_empty(): bool {
	if ( ! taxonomy_exists( OUTLET_STATUS_TAXONOMY ) ) {
		throw new \RuntimeException( 'Outlet status taxonomy does not exist.' );
	}

	$canonical_term = get_term_by( 'name', OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY );

	if ( ! $canonical_term ) {
		return true;
	}

	$query = new \WP_Query(
		array(
			'post_type'              => 'product',
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => OUTLET_STATUS_TAXONOMY,
					'field'    => 'term_id',
					'terms'    => $canonical_term->term_id,
				),
			),
		)
	);

	return ! $query->have_posts();
}

/**
 * Remove a product from the store’s outlet.
 *
 * @param \WC_Product $product Product to update.
 * @throws \RuntimeException If the outlet status taxonomy does not exist or term removal fails.
 * @since 1.0.0
 */
function remove_from_outlet( \WC_Product $product ): void {
	if ( ! taxonomy_exists( OUTLET_STATUS_TAXONOMY ) ) {
		throw new \RuntimeException( 'Outlet status taxonomy does not exist.' );
	}

	$result = wp_remove_object_terms( $product->get_id(), OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY );

	if ( is_wp_error( $result ) ) {
		throw new \RuntimeException( 'Failed to remove product from outlet.' );
	}
}

/**
 * Sets the store’s outlet status for a product.
 *
 * For performance, this function checks the currently stored state and only updates the
 * outlet status when a change in value is required.
 *
 * @param \WC_Product $product The product to update.
 * @param bool        $new_value Whether to include the product in the store’s outlet.
 * @throws \RuntimeException If setting the status fails.
 * @since 1.0.0
 */
function set_outlet( \WC_Product $product, bool $new_value ): void {
	// The currently stored state.
	$old_value = is_outlet( $product );

	if ( $old_value === $new_value ) {
		return; // No change needed.
	}

	if ( $new_value ) {
		add_to_outlet( $product );
	} else {
		remove_from_outlet( $product );
	}

	/**
	 * Fires when a product's outlet status changes.
	 *
	 * @since 1.0.0
	 *
	 * @param int  $product_id Product ID.
	 * @param bool $old_value  Previous outlet status.
	 * @param bool $new_value  New outlet status.
	 */
	do_action(
		'wc_outlet_status_changed',
		$product->get_id(),
		$old_value,
		$new_value
	);
}
