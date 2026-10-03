<?php
/**
 * Taxonomy-related functions.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Non-public taxonomy used to represent the outlet status of products.
 *
 * Used with a canonical term for internal flagging of products belonging
 * in the outlet.
 *
 * @internal
 */
const OUTLET_STATUS_TAXONOMY = 'outletpro_status';

/**
 * Private taxonomy providing facets for performant search.
 *
 * @internal
 */
const OUTLET_SEARCH_INDEX_TAXONOMY = 'outletpro_search_index';

/**
 * Canonical term for products belonging in the outlet.
 *
 * @internal
 */
const OUTLET_STATUS_CANONICAL_TERM = 'outlet';

/**
 * Default outlet discount tiers as percentages.
 *
 * @internal
 */
const DEFAULT_DISCOUNT_TIERS = array( 30, 50, 70 );

/**
 * Default outlet price tiers per currency
 *
 * @internal
 */
const DEFAULT_PRICE_TIERS = array(
	'AED' => array( 25, 50, 100 ),
	'AFN' => array( 500, 1000, 2500 ),
	'ALL' => array( 1000, 2500, 5000 ),
	'AMD' => array( 5000, 10000, 25000 ),
	'ANG' => array( 25, 50, 100 ),
	'AOA' => array( 10000, 25000, 50000 ),
	'ARS' => array( 10000, 25000, 50000 ),
	'AUD' => array( 15, 30, 50 ),
	'AWG' => array( 25, 50, 100 ),
	'AZN' => array( 25, 50, 100 ),
	'BAM' => array( 25, 50, 100 ),
	'BBD' => array( 25, 50, 100 ),
	'BDT' => array( 1000, 2500, 5000 ),
	'BGN' => array( 25, 50, 100 ),
	'BHD' => array( 10, 25, 50 ),
	'BIF' => array( 10000, 25000, 50000 ),
	'BMD' => array( 10, 25, 50 ),
	'BND' => array( 15, 30, 50 ),
	'BOB' => array( 100, 250, 500 ),
	'BRL' => array( 50, 100, 200 ),
	'BSD' => array( 10, 25, 50 ),
	'BTN' => array( 500, 1000, 2500 ),
	'BWP' => array( 250, 500, 1000 ),
	'BYN' => array( 25, 50, 100 ),
	'BZD' => array( 25, 50, 100 ),
	'CAD' => array( 15, 30, 50 ),
	'CDF' => array( 10000, 25000, 50000 ),
	'CHF' => array( 10, 20, 40 ),
	'CLP' => array( 10000, 25000, 50000 ),
	'CNY' => array( 100, 250, 500 ),
	'COP' => array( 100000, 250000, 500000 ),
	'CRC' => array( 10000, 25000, 50000 ),
	'CUP' => array( 250, 500, 1000 ),
	'CVE' => array( 1000, 2500, 5000 ),
	'CZK' => array( 250, 500, 1000 ),
	'DJF' => array( 1000, 3000, 5000 ),
	'DKK' => array( 100, 250, 500 ),
	'DOP' => array( 1000, 2500, 5000 ),
	'DZD' => array( 1000, 2500, 5000 ),
	'EGP' => array( 500, 1000, 2500 ),
	'ERN' => array( 250, 500, 1000 ),
	'ETB' => array( 1000, 2500, 5000 ),
	'EUR' => array( 10, 25, 50 ),
	'FJD' => array( 25, 50, 100 ),
	'FKP' => array( 10, 20, 40 ),
	'GBP' => array( 10, 20, 40 ),
	'GEL' => array( 25, 50, 100 ),
	'GHS' => array( 250, 500, 1000 ),
	'GIP' => array( 10, 20, 40 ),
	'GMD' => array( 1000, 2500, 5000 ),
	'GNF' => array( 100000, 250000, 500000 ),
	'GTQ' => array( 100, 250, 500 ),
	'GYD' => array( 5000, 10000, 25000 ),
	'HKD' => array( 100, 250, 500 ),
	'HNL' => array( 500, 1000, 2500 ),
	'HRK' => array( 100, 250, 500 ),
	'HTG' => array( 1000, 2500, 5000 ),
	'HUF' => array( 5000, 10000, 25000 ),
	'IDR' => array( 100000, 250000, 500000 ),
	'ILS' => array( 50, 100, 200 ),
	'INR' => array( 500, 1000, 2500 ),
	'IQD' => array( 10000, 25000, 50000 ),
	'IRR' => array( 1000000, 2500000, 5000000 ),
	'ISK' => array( 1000, 3000, 5000 ),
	'JMD' => array( 5000, 10000, 25000 ),
	'JOD' => array( 10, 25, 50 ),
	'JPY' => array( 1000, 3000, 5000 ),
	'KES' => array( 1000, 2500, 5000 ),
	'KGS' => array( 1000, 2500, 5000 ),
	'KHR' => array( 10000, 25000, 50000 ),
	'KMF' => array( 1000, 3000, 5000 ),
	'KRW' => array( 10000, 30000, 50000 ),
	'KWD' => array( 10, 25, 50 ),
	'KYD' => array( 10, 25, 50 ),
	'KZT' => array( 5000, 10000, 25000 ),
	'LAK' => array( 100000, 250000, 500000 ),
	'LBP' => array( 1000000, 2500000, 5000000 ),
	'LKR' => array( 5000, 10000, 25000 ),
	'LRD' => array( 5000, 10000, 25000 ),
	'LSL' => array( 250, 500, 1000 ),
	'LYD' => array( 25, 50, 100 ),
	'MAD' => array( 100, 250, 500 ),
	'MDL' => array( 250, 500, 1000 ),
	'MGA' => array( 10000, 25000, 50000 ),
	'MKD' => array( 1000, 2500, 5000 ),
	'MMK' => array( 10000, 25000, 50000 ),
	'MNT' => array( 10000, 25000, 50000 ),
	'MOP' => array( 100, 250, 500 ),
	'MRU' => array( 1000, 2500, 5000 ),
	'MUR' => array( 1000, 2500, 5000 ),
	'MVR' => array( 250, 500, 1000 ),
	'MWK' => array( 10000, 25000, 50000 ),
	'MXN' => array( 200, 500, 1000 ),
	'MYR' => array( 50, 100, 200 ),
	'MZN' => array( 1000, 2500, 5000 ),
	'NAD' => array( 250, 500, 1000 ),
	'NGN' => array( 10000, 25000, 50000 ),
	'NIO' => array( 1000, 2500, 5000 ),
	'NOK' => array( 100, 250, 500 ),
	'NPR' => array( 500, 1000, 2500 ),
	'NZD' => array( 15, 30, 50 ),
	'OMR' => array( 10, 25, 50 ),
	'PAB' => array( 10, 25, 50 ),
	'PEN' => array( 50, 100, 200 ),
	'PGK' => array( 50, 100, 200 ),
	'PHP' => array( 1000, 2500, 5000 ),
	'PKR' => array( 10000, 25000, 50000 ),
	'PLN' => array( 50, 100, 200 ),
	'PYG' => array( 100000, 250000, 500000 ),
	'QAR' => array( 50, 100, 200 ),
	'RON' => array( 50, 100, 200 ),
	'RSD' => array( 1000, 2500, 5000 ),
	'RUB' => array( 1000, 2500, 5000 ),
	'RWF' => array( 10000, 25000, 50000 ),
	'SAR' => array( 50, 100, 200 ),
	'SBD' => array( 100, 250, 500 ),
	'SCR' => array( 250, 500, 1000 ),
	'SEK' => array( 100, 250, 500 ),
	'SGD' => array( 15, 30, 50 ),
	'SHP' => array( 10, 20, 40 ),
	'SLE' => array( 250, 500, 1000 ),
	'SLL' => array( 10000, 25000, 50000 ),
	'SOS' => array( 10000, 25000, 50000 ),
	'SRD' => array( 1000, 2500, 5000 ),
	'SSP' => array( 10000, 25000, 50000 ),
	'STN' => array( 250, 500, 1000 ),
	'SVC' => array( 10, 25, 50 ),
	'SYP' => array( 100000, 250000, 500000 ),
	'SZL' => array( 250, 500, 1000 ),
	'THB' => array( 500, 1000, 2500 ),
	'TJS' => array( 250, 500, 1000 ),
	'TMT' => array( 50, 100, 200 ),
	'TND' => array( 50, 100, 200 ),
	'TOP' => array( 25, 50, 100 ),
	'TRY' => array( 500, 1000, 2500 ),
	'TTD' => array( 100, 250, 500 ),
	'TWD' => array( 500, 1000, 2500 ),
	'TZS' => array( 10000, 25000, 50000 ),
	'UAH' => array( 1000, 2500, 5000 ),
	'UGX' => array( 10000, 25000, 50000 ),
	'USD' => array( 10, 25, 50 ),
	'UYU' => array( 1000, 2500, 5000 ),
	'UZS' => array( 10000, 25000, 50000 ),
	'VES' => array( 500, 1000, 2500 ),
	'VND' => array( 100000, 250000, 500000 ),
	'VUV' => array( 1000, 3000, 5000 ),
	'WST' => array( 25, 50, 100 ),
	'XAF' => array( 10000, 25000, 50000 ),
	'XCD' => array( 25, 50, 100 ),
	'XOF' => array( 10000, 25000, 50000 ),
	'XPF' => array( 10000, 25000, 50000 ),
	'YER' => array( 10000, 25000, 50000 ),
	'ZAR' => array( 250, 500, 1000 ),
	'ZMW' => array( 250, 500, 1000 ),
	'ZWL' => array( 10000, 25000, 50000 ),
);

/**
 * Helper to initialize taxonomies.
 *
 * @internal
 */
function init_taxonomies(): void {
	register_outlet_status_taxonomy();
	register_outlet_search_index_taxonomy();
}

/**
 * Helper to de-initialize taxonomies.
 *
 * @internal
 */
function deinit_taxonomies(): void {
	unregister_taxonomy( OUTLET_STATUS_TAXONOMY );
	unregister_taxonomy( OUTLET_SEARCH_INDEX_TAXONOMY );
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
			__( 'Outlet status taxonomy registered', 'outletpro' ),
			$taxonomy_exists ? __( 'Yes', 'outletpro' ) : __( 'No', 'outletpro' ),
		),
		'outlet-canonical-term-id'   => array(
			__( 'Canonical term ID', 'outletpro' ),
			$canonical_term instanceof \WP_Term ? $canonical_term->term_id : __( 'Not found', 'outletpro' ),
		),
		'outlet-product-count'       => array(
			__( 'Total products in outlet', 'outletpro' ),
			$outlet_product_count ?? __( 'Unknown', 'outletpro' ),
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
		'label'        => __( 'Outlet Status', 'outletpro' ),
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
 * Registers the outlet search index taxonomy.
 *
 * @internal
 */
function register_outlet_search_index_taxonomy(): void {
	$args = array(
		'label'        => __( 'Outlet Search Indexes', 'outletpro' ),
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

	register_taxonomy( OUTLET_SEARCH_INDEX_TAXONOMY, 'product', $args );
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
 * Seed the outlet search index taxonomy with default terms.
 *
 * @internal
 * @throws \RuntimeException If the term seeding fails.
 */
function seed_outlet_search_index_taxonomy(): void {
	$terms = array();
	foreach ( DEFAULT_DISCOUNT_TIERS as $discount ) {
		$terms[] = 'outlet-discount-' . $discount;
	}
	$price_tiers = DEFAULT_PRICE_TIERS[ get_woocommerce_currency() ] ?? DEFAULT_PRICE_TIERS['USD'];
	foreach ( $price_tiers as $price ) {
		$terms[] = 'outlet-price-' . $price;
	}

	foreach ( $terms as $term ) {
		if ( term_exists( $term, OUTLET_SEARCH_INDEX_TAXONOMY ) ) {
			continue;
		}

		$result = wp_insert_term( $term, OUTLET_SEARCH_INDEX_TAXONOMY );

		if ( is_wp_error( $result ) ) {
			throw new \RuntimeException( 'Failed to seed outlet search index taxonomy.' );
		}
	}
}

/**
 * Remove all terms in the outlet status taxonomy.
 *
 * @throws \RuntimeException If terms cannot be retrieved.
 * @internal
 */
function truncate_outlet_status_taxonomy(): void {
	$terms = get_terms(
		array(
			'taxonomy'   => OUTLET_STATUS_TAXONOMY,
			'hide_empty' => false,
			'fields'     => 'ids',
		)
	);
	if ( is_wp_error( $terms ) ) {
		throw new \RuntimeException( 'Outlet status terms could not be retrieved.' );
	}
	foreach ( $terms as $term_id ) {
		wp_delete_term( $term_id, OUTLET_STATUS_TAXONOMY );
	}
}

/**
 * Remove all terms in the outlet search index taxonomy.
 *
 * @throws \RuntimeException If terms cannot be retrieved.
 * @internal
 */
function truncate_outlet_search_index_taxonomy(): void {
	$terms = get_terms(
		array(
			'taxonomy'   => OUTLET_SEARCH_INDEX_TAXONOMY,
			'hide_empty' => false,
			'fields'     => 'ids',
		)
	);
	if ( is_wp_error( $terms ) ) {
		throw new \RuntimeException( 'Outlet search index terms could not be retrieved.' );
	}
	foreach ( $terms as $term_id ) {
		wp_delete_term( $term_id, OUTLET_SEARCH_INDEX_TAXONOMY );
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
 * Schedules a search index update after adding the product.
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

	as_enqueue_async_action( 'outletpro_reindex_search_facets', array( $product->get_id() ), 'outletpro', true );
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
 * Schedules clearing the search index after removal.
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

	as_enqueue_async_action( 'outletpro_reindex_search_facets', array( $product->get_id() ), 'outletpro', true );
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
		'outletpro_status_changed',
		$product->get_id(),
		$old_value,
		$new_value
	);
}

/**
 * Get the outlet price tiers in numerical order.
 *
 * @return int[] Price tier amounts
 * @throws \RuntimeException If the price tiers cannot be retrieved.
 * @internal
 */
function get_price_tiers(): array {
	$terms = get_terms(
		array(
			'taxonomy'   => OUTLET_SEARCH_INDEX_TAXONOMY,
			'hide_empty' => false,
			'fields'     => 'slugs',
		)
	);
	if ( is_wp_error( $terms ) ) {
		throw new \RuntimeException( 'Outlet price tiers could not be retrieved.' );
	}

	$tiers = array_filter(
		$terms,
		static function ( string $term ): bool {
			return 0 === strpos( $term, 'outlet-price-' );
		}
	);

	$amounts = array_map(
		static function ( string $term ): ?int {
			$amount = filter_var( substr( $term, strlen( 'outlet-price-' ) ), FILTER_VALIDATE_INT );
			return false === $amount ? null : $amount;
		},
		$tiers
	);
	$amounts = array_filter(
		$amounts,
		static function ( ?int $amount ): bool {
			return null !== $amount;
		}
	);
	sort( $amounts, SORT_NUMERIC );

	return $amounts;
}

/**
 * Get the outlet discount tiers in numerical order.
 *
 * @return int[] Discount tier amounts
 * @throws \RuntimeException If the discount tiers cannot be retrieved.
 * @internal
 */
function get_discount_tiers(): array {
	$terms = get_terms(
		array(
			'taxonomy'   => OUTLET_SEARCH_INDEX_TAXONOMY,
			'hide_empty' => false,
			'fields'     => 'slugs',
		)
	);
	if ( is_wp_error( $terms ) ) {
		throw new \RuntimeException( 'Outlet discount tiers could not be retrieved.' );
	}

	$tiers   = array_filter(
		$terms,
		static function ( string $term ): bool {
			return 0 === strpos( $term, 'outlet-discount-' );
		}
	);
	$amounts = array_map(
		static function ( string $term ): ?int {
			$amount = filter_var( substr( $term, strlen( 'outlet-discount-' ) ), FILTER_VALIDATE_INT );
			return false === $amount ? null : $amount;
		},
		$tiers
	);

	$amounts = array_filter(
		$amounts,
		static function ( ?int $amount ): bool {
			return null !== $amount;
		}
	);
	sort( $amounts, SORT_NUMERIC );

	return $amounts;
}
