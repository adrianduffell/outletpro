<?php
/**
 * Taxonomy-related functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Non-public taxonomy used to represent the clearance status of products.
 *
 * Used with a canonical term for internal flagging of products belonging
 * in the clearance section.
 */
const CLEARANCE_STATUS_TAXONOMY = 'wc_clearance_status';

/**
 * Canonical term for products belonging in the clearance section.
 */
const CLEARANCE_STATUS_CANONICAL_TERM = 'clearance';

/**
 * Helper to initialize taxonomies.
 *
 * @internal
 */
function init_taxonomies(): void {
	register_clearance_status_taxonomy();
}

/**
 * Helper to report diagnostic info on taxonomies.
 *
 * @internal
 * @return array<string, array{0: string, 1: int|string}>
 */
function report_taxonomies(): array {
	$taxonomy_exists         = taxonomy_exists( CLEARANCE_STATUS_TAXONOMY );
	$canonical_term          = $taxonomy_exists ? get_term_by( 'name', CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY ) : null;
	$clearance_product_count = $taxonomy_exists ? count_clearance() : null;

	return array(
		'clearance-taxonomy-registered' => array(
			__( 'Clearance status taxonomy registered', 'wc-clearance' ),
			$taxonomy_exists ? __( 'Yes', 'wc-clearance' ) : __( 'No', 'wc-clearance' ),
		),
		'clearance-canonical-term-id'   => array(
			__( 'Canonical term ID', 'wc-clearance' ),
			$canonical_term instanceof \WP_Term ? $canonical_term->term_id : __( 'Not found', 'wc-clearance' ),
		),
		'clearance-product-count'       => array(
			__( 'Total products in clearance section', 'wc-clearance' ),
			$clearance_product_count ?? __( 'Unknown', 'wc-clearance' ),
		),
	);
}

/**
 * Register the clearance status taxonomy.
 *
 * @since 1.0.0
 */
function register_clearance_status_taxonomy(): void {
	$args = array(
		'label'        => __( 'Clearance Status', 'wc-clearance' ),
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

	register_taxonomy( CLEARANCE_STATUS_TAXONOMY, 'product', $args );
}

/**
 * Seed the clearance status taxonomy with the canonical term.
 *
 * @throws \RuntimeException If the term seeding fails.
 * @since 1.0.0
 */
function seed_clearance_status_taxonomy(): void {
	if ( term_exists( CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY ) ) {
		return;
	}

	$result = wp_insert_term( CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

	if ( is_wp_error( $result ) ) {
		throw new \RuntimeException(
			sprintf(
				'Failed to seed clearance status taxonomy. %s',
				$result->get_error_message()
			)
		);
	}
}

/**
 * Check if a product is in the clearance section.
 *
 * @param \WC_Product $product The product to check.
 * @throws \RuntimeException If the clearance status taxonomy does not exist.
 * @throws \RuntimeException If a variation's parent product cannot be found.
 * @since 1.0.0
 */
function is_clearance( \WC_Product $product ): bool {
	if ( ! taxonomy_exists( CLEARANCE_STATUS_TAXONOMY ) ) {
		throw new \RuntimeException( 'Clearance status taxonomy does not exist.' );
	}

	// Handle variations by checking the parent product.
	if ( $product->is_type( 'variation' ) ) {
		$parent = wc_get_product( $product->get_parent_id() );
		if ( ! $parent ) {
			throw new \RuntimeException(
				sprintf(
					'Parent product (ID %d) for variation (ID %d) could not be found.',
					$product->get_parent_id(),
					$product->get_id()
				)
			);
		}
		return is_clearance( $parent );
	}

	return has_term( CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY, $product->get_id() );
}

/**
 * Add a product to the clearance section.
 *
 * @param \WC_Product $product Product to update.
 * @throws \RuntimeException If the clearance status taxonomy does not exist or the term assignment fails.
 * @since 1.0.0
 */
function add_to_clearance( \WC_Product $product ): void {
	if ( ! taxonomy_exists( CLEARANCE_STATUS_TAXONOMY ) ) {
		throw new \RuntimeException( 'Clearance status taxonomy does not exist.' );
	}

	$result = wp_set_object_terms( $product->get_id(), CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

	if ( is_wp_error( $result ) ) {
		throw new \RuntimeException(
			sprintf(
				'Failed to assign clearance status term to product ID %d. %s',
				$product->get_id(),
				$result->get_error_message()
			)
		);
	}
}

/**
 * Count the number of published products in the clearance section.
 *
 * @throws \RuntimeException If the clearance status taxonomy does not exist.
 * @since 1.0.0
 */
function count_clearance(): int {
	if ( ! taxonomy_exists( CLEARANCE_STATUS_TAXONOMY ) ) {
		throw new \RuntimeException( 'Clearance status taxonomy does not exist.' );
	}

	$canonical_term = get_term_by( 'name', CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

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
					'taxonomy' => CLEARANCE_STATUS_TAXONOMY,
					'field'    => 'term_id',
					'terms'    => $canonical_term->term_id,
				),
			),
		)
	);

	return $query->found_posts;
}

/**
 * Check if the clearance section is empty.
 *
 * More performant than count_clearance() because it uses no_found_rows to skip the SQL row count.
 *
 * @throws \RuntimeException If the clearance status taxonomy does not exist.
 * @since 1.0.0
 */
function clearance_section_empty(): bool {
	if ( ! taxonomy_exists( CLEARANCE_STATUS_TAXONOMY ) ) {
		throw new \RuntimeException( 'Clearance status taxonomy does not exist.' );
	}

	$canonical_term = get_term_by( 'name', CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

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
					'taxonomy' => CLEARANCE_STATUS_TAXONOMY,
					'field'    => 'term_id',
					'terms'    => $canonical_term->term_id,
				),
			),
		)
	);

	return ! $query->have_posts();
}

/**
 * Remove a product from the clearance section.
 *
 * @param \WC_Product $product Product to update.
 * @throws \RuntimeException If the clearance status taxonomy does not exist or term removal fails.
 * @since 1.0.0
 */
function remove_from_clearance( \WC_Product $product ): void {
	if ( ! taxonomy_exists( CLEARANCE_STATUS_TAXONOMY ) ) {
		throw new \RuntimeException( 'Clearance status taxonomy does not exist.' );
	}

	$result = wp_remove_object_terms( $product->get_id(), CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

	if ( is_wp_error( $result ) ) {
		throw new \RuntimeException(
			sprintf(
				'Failed to remove product %d from clearance. %s',
				$product->get_id(),
				$result->get_error_message()
			)
		);
	}
}

/**
 * Sets the clearance section status for a product.
 *
 * For performance, this function checks the currently stored state and only updates the
 * clearance status when a change in value is required.
 *
 * @param \WC_Product $product The product to update.
 * @param bool        $new_value Whether to include the product in the clearance section.
 * @throws \RuntimeException If setting the status fails.
 * @since 1.0.0
 */
function set_clearance( \WC_Product $product, bool $new_value ): void {
	// The currently stored state.
	$old_value = is_clearance( $product );

	if ( $old_value === $new_value ) {
		return; // No change needed.
	}

	if ( $new_value ) {
		add_to_clearance( $product );
	} else {
		remove_from_clearance( $product );
	}

	/**
	 * Fires when a product's clearance section status changes.
	 *
	 * @since 1.0.0
	 *
	 * @param int  $product_id Product ID.
	 * @param bool $old_value  Previous clearance section status.
	 * @param bool $new_value  New clearance section status.
	 */
	do_action(
		'wc_clearance_status_changed',
		$product->get_id(),
		$old_value,
		$new_value
	);
}
