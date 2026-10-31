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
	'AED' => array( 25, 50, 100, 200 ),
	'AFN' => array( 500, 1000, 2500, 5000 ),
	'ALL' => array( 1000, 2500, 5000, 10000 ),
	'AMD' => array( 5000, 10000, 25000, 50000 ),
	'ANG' => array( 25, 50, 100, 200 ),
	'AOA' => array( 10000, 25000, 50000, 100000 ),
	'ARS' => array( 10000, 25000, 50000, 100000 ),
	'AUD' => array( 15, 30, 50, 100 ),
	'AWG' => array( 25, 50, 100, 200 ),
	'AZN' => array( 25, 50, 100, 200 ),
	'BAM' => array( 25, 50, 100, 200 ),
	'BBD' => array( 25, 50, 100, 200 ),
	'BDT' => array( 1000, 2500, 5000, 10000 ),
	'BGN' => array( 25, 50, 100, 200 ),
	'BHD' => array( 10, 25, 50, 100 ),
	'BIF' => array( 10000, 25000, 50000, 100000 ),
	'BMD' => array( 10, 25, 50, 100 ),
	'BND' => array( 15, 30, 50, 100 ),
	'BOB' => array( 100, 250, 500, 1000 ),
	'BRL' => array( 50, 100, 200, 400 ),
	'BSD' => array( 10, 25, 50, 100 ),
	'BTN' => array( 500, 1000, 2500, 5000 ),
	'BWP' => array( 250, 500, 1000, 2000 ),
	'BYN' => array( 25, 50, 100, 200 ),
	'BZD' => array( 25, 50, 100, 200 ),
	'CAD' => array( 15, 30, 50, 100 ),
	'CDF' => array( 10000, 25000, 50000, 100000 ),
	'CHF' => array( 10, 20, 40, 75 ),
	'CLP' => array( 10000, 25000, 50000, 100000 ),
	'CNY' => array( 100, 250, 500, 1000 ),
	'COP' => array( 100000, 250000, 500000, 1000000 ),
	'CRC' => array( 10000, 25000, 50000, 100000 ),
	'CUP' => array( 250, 500, 1000, 2000 ),
	'CVE' => array( 1000, 2500, 5000, 10000 ),
	'CZK' => array( 250, 500, 1000, 2000 ),
	'DJF' => array( 1000, 3000, 5000, 10000 ),
	'DKK' => array( 100, 250, 500, 1000 ),
	'DOP' => array( 1000, 2500, 5000, 10000 ),
	'DZD' => array( 1000, 2500, 5000, 10000 ),
	'EGP' => array( 500, 1000, 2500, 5000 ),
	'ERN' => array( 250, 500, 1000, 2000 ),
	'ETB' => array( 1000, 2500, 5000, 10000 ),
	'EUR' => array( 10, 25, 50, 100 ),
	'FJD' => array( 25, 50, 100, 200 ),
	'FKP' => array( 10, 20, 40, 75 ),
	'GBP' => array( 10, 20, 40, 75 ),
	'GEL' => array( 25, 50, 100, 200 ),
	'GHS' => array( 250, 500, 1000, 2000 ),
	'GIP' => array( 10, 20, 40, 75 ),
	'GMD' => array( 1000, 2500, 5000, 10000 ),
	'GNF' => array( 100000, 250000, 500000, 1000000 ),
	'GTQ' => array( 100, 250, 500, 1000 ),
	'GYD' => array( 5000, 10000, 25000, 50000 ),
	'HKD' => array( 100, 250, 500, 1000 ),
	'HNL' => array( 500, 1000, 2500, 5000 ),
	'HRK' => array( 100, 250, 500, 1000 ),
	'HTG' => array( 1000, 2500, 5000, 10000 ),
	'HUF' => array( 5000, 10000, 25000, 50000 ),
	'IDR' => array( 100000, 250000, 500000, 1000000 ),
	'ILS' => array( 50, 100, 200, 400 ),
	'INR' => array( 500, 1000, 2500, 5000 ),
	'IQD' => array( 10000, 25000, 50000, 100000 ),
	'IRR' => array( 1000000, 2500000, 5000000, 10000000 ),
	'ISK' => array( 1000, 3000, 5000, 10000 ),
	'JMD' => array( 5000, 10000, 25000, 50000 ),
	'JOD' => array( 10, 25, 50, 100 ),
	'JPY' => array( 1000, 3000, 5000, 10000 ),
	'KES' => array( 1000, 2500, 5000, 10000 ),
	'KGS' => array( 1000, 2500, 5000, 10000 ),
	'KHR' => array( 10000, 25000, 50000, 100000 ),
	'KMF' => array( 1000, 3000, 5000, 10000 ),
	'KRW' => array( 10000, 30000, 50000, 100000 ),
	'KWD' => array( 10, 25, 50, 100 ),
	'KYD' => array( 10, 25, 50, 100 ),
	'KZT' => array( 5000, 10000, 25000, 50000 ),
	'LAK' => array( 100000, 250000, 500000, 1000000 ),
	'LBP' => array( 1000000, 2500000, 5000000, 10000000 ),
	'LKR' => array( 5000, 10000, 25000, 50000 ),
	'LRD' => array( 5000, 10000, 25000, 50000 ),
	'LSL' => array( 250, 500, 1000, 2000 ),
	'LYD' => array( 25, 50, 100, 200 ),
	'MAD' => array( 100, 250, 500, 1000 ),
	'MDL' => array( 250, 500, 1000, 2000 ),
	'MGA' => array( 10000, 25000, 50000, 100000 ),
	'MKD' => array( 1000, 2500, 5000, 10000 ),
	'MMK' => array( 10000, 25000, 50000, 100000 ),
	'MNT' => array( 10000, 25000, 50000, 100000 ),
	'MOP' => array( 100, 250, 500, 1000 ),
	'MRU' => array( 1000, 2500, 5000, 10000 ),
	'MUR' => array( 1000, 2500, 5000, 10000 ),
	'MVR' => array( 250, 500, 1000, 2000 ),
	'MWK' => array( 10000, 25000, 50000, 100000 ),
	'MXN' => array( 200, 500, 1000, 2000 ),
	'MYR' => array( 50, 100, 200, 400 ),
	'MZN' => array( 1000, 2500, 5000, 10000 ),
	'NAD' => array( 250, 500, 1000, 2000 ),
	'NGN' => array( 10000, 25000, 50000, 100000 ),
	'NIO' => array( 1000, 2500, 5000, 10000 ),
	'NOK' => array( 100, 250, 500, 1000 ),
	'NPR' => array( 500, 1000, 2500, 5000 ),
	'NZD' => array( 15, 30, 50, 100 ),
	'OMR' => array( 10, 25, 50, 100 ),
	'PAB' => array( 10, 25, 50, 100 ),
	'PEN' => array( 50, 100, 200, 400 ),
	'PGK' => array( 50, 100, 200, 400 ),
	'PHP' => array( 1000, 2500, 5000, 10000 ),
	'PKR' => array( 10000, 25000, 50000, 100000 ),
	'PLN' => array( 50, 100, 200, 400 ),
	'PYG' => array( 100000, 250000, 500000, 1000000 ),
	'QAR' => array( 50, 100, 200, 400 ),
	'RON' => array( 50, 100, 200, 400 ),
	'RSD' => array( 1000, 2500, 5000, 10000 ),
	'RUB' => array( 1000, 2500, 5000, 10000 ),
	'RWF' => array( 10000, 25000, 50000, 100000 ),
	'SAR' => array( 50, 100, 200, 400 ),
	'SBD' => array( 100, 250, 500, 1000 ),
	'SCR' => array( 250, 500, 1000, 2000 ),
	'SEK' => array( 100, 250, 500, 1000 ),
	'SGD' => array( 15, 30, 50, 100 ),
	'SHP' => array( 10, 20, 40, 75 ),
	'SLE' => array( 250, 500, 1000, 2000 ),
	'SLL' => array( 10000, 25000, 50000, 100000 ),
	'SOS' => array( 10000, 25000, 50000, 100000 ),
	'SRD' => array( 1000, 2500, 5000, 10000 ),
	'SSP' => array( 10000, 25000, 50000, 100000 ),
	'STN' => array( 250, 500, 1000, 2000 ),
	'SVC' => array( 10, 25, 50, 100 ),
	'SYP' => array( 100000, 250000, 500000, 1000000 ),
	'SZL' => array( 250, 500, 1000, 2000 ),
	'THB' => array( 500, 1000, 2500, 5000 ),
	'TJS' => array( 250, 500, 1000, 2000 ),
	'TMT' => array( 50, 100, 200, 400 ),
	'TND' => array( 50, 100, 200, 400 ),
	'TOP' => array( 25, 50, 100, 200 ),
	'TRY' => array( 500, 1000, 2500, 5000 ),
	'TTD' => array( 100, 250, 500, 1000 ),
	'TWD' => array( 500, 1000, 2500, 5000 ),
	'TZS' => array( 10000, 25000, 50000, 100000 ),
	'UAH' => array( 1000, 2500, 5000, 10000 ),
	'UGX' => array( 10000, 25000, 50000, 100000 ),
	'USD' => array( 10, 25, 50, 100 ),
	'UYU' => array( 1000, 2500, 5000, 10000 ),
	'UZS' => array( 10000, 25000, 50000, 100000 ),
	'VES' => array( 500, 1000, 2500, 5000 ),
	'VND' => array( 100000, 250000, 500000, 1000000 ),
	'VUV' => array( 1000, 3000, 5000, 10000 ),
	'WST' => array( 25, 50, 100, 200 ),
	'XAF' => array( 10000, 25000, 50000, 100000 ),
	'XCD' => array( 25, 50, 100, 200 ),
	'XOF' => array( 10000, 25000, 50000, 100000 ),
	'XPF' => array( 10000, 25000, 50000, 100000 ),
	'YER' => array( 10000, 25000, 50000, 100000 ),
	'ZAR' => array( 250, 500, 1000, 2000 ),
	'ZMW' => array( 250, 500, 1000, 2000 ),
	'ZWL' => array( 10000, 25000, 50000, 100000 ),
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

	$report = array(
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

	$report['outlet-search-index-terms'] = array(
		__( 'Search index', 'outletpro' ),
		__( 'Unknown', 'outletpro' ),
	);

	if ( ! taxonomy_exists( OUTLET_SEARCH_INDEX_TAXONOMY ) ) {
		return $report;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => OUTLET_SEARCH_INDEX_TAXONOMY,
			'hide_empty' => false,
		)
	);
	if ( is_wp_error( $terms ) ) {
		\wc_get_logger()->error( 'Outlet search index terms could not be retrieved.' );
		return $report;
	}
	if ( ! $terms ) {
		$report['outlet-search-index-terms'][1] = __( 'None', 'outletpro' );
		return $report;
	}

	$facets = array();
	foreach ( $terms as $term ) {
		/* translators: 1: Search index term name, 2: Product count. */
		$facets[] = sprintf( __( '%1$s (%2$d)', 'outletpro' ), $term->name, $term->count );
	}
	$report['outlet-search-index-terms'][1] = implode( ', ', $facets );

	return $report;
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
 * Seeding is not performed if the taxonomy contains terms.
 *
 * @internal
 * @throws \RuntimeException If the term seeding fails.
 */
function seed_outlet_search_index_taxonomy(): void {
	$existing_terms = get_terms(
		array(
			'taxonomy'   => OUTLET_SEARCH_INDEX_TAXONOMY,
			'hide_empty' => false,
			'fields'     => 'ids',
			'number'     => 1,
		)
	);
	if ( is_wp_error( $existing_terms ) ) {
		throw new \RuntimeException( 'Outlet search index terms could not be retrieved.' );
	}
	if ( $existing_terms ) {
		return;
	}

	// Pause search index during seeding.
	remove_action( 'created_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook' );
	remove_action( 'edited_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook' );
	remove_action( 'delete_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook' );

	foreach ( DEFAULT_DISCOUNT_TIERS as $discount ) {
		add_discount_tier( $discount );
	}
	$price_tiers = DEFAULT_PRICE_TIERS[ get_woocommerce_currency() ] ?? DEFAULT_PRICE_TIERS['USD'];
	foreach ( $price_tiers as $price ) {
		add_price_tier( $price );
	}

	// Manually re-index products.
	reindex_all_outlet_products_hook();

	// Unpause search index.
	add_action( 'created_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook' );
	add_action( 'edited_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook' );
	add_action( 'delete_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook' );
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

	as_enqueue_async_action( 'outletpro_reindex_search_facets', array( $product->get_id() ), 'outletpro' );
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

	as_enqueue_async_action( 'outletpro_reindex_search_facets', array( $product->get_id() ), 'outletpro' );
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
 * Returns the human-readable name for a search index facet.
 *
 * @internal
 * @param \WP_Term $term Search index facet term.
 * @throws \InvalidArgumentException If the term is not a valid search index facet.
 */
function get_search_index_facet_nice_name( \WP_Term $term ): string {
	if ( OUTLET_SEARCH_INDEX_TAXONOMY !== $term->taxonomy ) {
		throw new \InvalidArgumentException( 'Term is not an outlet search index facet.' );
	}
	if ( 1 !== preg_match( '/^outlet-([a-z]+)-([0-9]+)$/D', $term->slug, $matches ) ) {
		throw new \InvalidArgumentException( 'Invalid search index facet slug.' );
	}
	$amount = filter_var( $matches[2], FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 0 ) ) );
	if ( false === $amount ) {
		throw new \InvalidArgumentException( 'Invalid search index facet amount.' );
	}

	if ( 'price' === $matches[1] ) {
		// translators: %s: Formatted price tier amount.
		$name = html_entity_decode( wp_strip_all_tags( sprintf( __( 'Under %s', 'outletpro' ), wc_price( $amount, array( 'decimals' => 0 ) ) ) ), ENT_QUOTES, 'UTF-8' );
		return $name;
	}
	if ( 'discount' === $matches[1] ) {
		// translators: %d: Discount tier percentage.
		return sprintf( __( '%d%% off', 'outletpro' ), $amount );
	}

	throw new \InvalidArgumentException( 'Unsupported search index facet type.' );
}

/**
 * Batch additions, updates, and deletions of outlet price tiers.
 *
 * Validates all operations before writing and requests one reindex afterward.
 *
 * @param int[]           $add Price thresholds to add.
 * @param array<int, int> $update Existing thresholds mapped to new thresholds.
 * @param int[]           $delete Price thresholds to delete.
 * @throws \InvalidArgumentException If an amount is not a non-negative integer.
 * @throws \RuntimeException If a tier operation is invalid or fails.
 * @since 1.1.0
 */
function batch_price_tiers( array $add = array(), array $update = array(), array $delete = array() ): void {
	if ( empty( $add ) && empty( $update ) && empty( $delete ) ) {
		return;
	}
	validate_tier_batch( $add, $update, $delete, get_price_tiers() );
	apply_tier_batch( $add, $update, $delete, 'OutletPro\add_price_tier', 'OutletPro\update_price_tier', 'OutletPro\delete_price_tier' );
}

/**
 * Add an outlet price tier.
 *
 * @param int $price Non-negative price threshold in the store's currency.
 * @throws \InvalidArgumentException If the price is negative.
 * @throws \RuntimeException If the tier cannot be added, including duplicates.
 * @since 1.1.0
 */
function add_price_tier( int $price ): void {
	if ( $price < 0 ) {
		throw new \InvalidArgumentException( 'Outlet price tier must not be negative.' );
	}
	$slug = 'outlet-price-' . $price;
	if ( get_term_by( 'slug', $slug, OUTLET_SEARCH_INDEX_TAXONOMY ) ) {
		throw new \RuntimeException( 'Outlet price tier could not be added.' );
	}
	$result = wp_insert_term( $slug, OUTLET_SEARCH_INDEX_TAXONOMY );
	if ( is_wp_error( $result ) ) {
		throw new \RuntimeException( 'Outlet price tier could not be added.' );
	}
}

/**
 * Update an outlet price tier, preserving its product assignments.
 *
 * @param int $price Existing price threshold.
 * @param int $new_price Non-negative replacement price threshold.
 * @throws \InvalidArgumentException If either price is negative.
 * @throws \RuntimeException If the tier cannot be found or updated.
 * @since 1.1.0
 */
function update_price_tier( int $price, int $new_price ): void {
	if ( $price < 0 ) {
		throw new \InvalidArgumentException( 'Outlet price tier must not be negative.' );
	}
	if ( $new_price < 0 ) {
		throw new \InvalidArgumentException( 'Outlet price tier must not be negative.' );
	}
	$term = get_term_by( 'slug', 'outlet-price-' . $price, OUTLET_SEARCH_INDEX_TAXONOMY );
	if ( ! $term ) {
		throw new \RuntimeException( 'Outlet price tier could not be found.' );
	}
	$slug   = 'outlet-price-' . $new_price;
	$result = wp_update_term(
		$term->term_id,
		OUTLET_SEARCH_INDEX_TAXONOMY,
		array(
			'name' => $slug,
			'slug' => $slug,
		)
	);
	if ( is_wp_error( $result ) ) {
		throw new \RuntimeException( 'Outlet price tier could not be updated.' );
	}
}

/**
 * Delete an outlet price tier and its product assignments.
 *
 * @param int $price Price threshold to delete.
 * @throws \InvalidArgumentException If the price is negative.
 * @throws \RuntimeException If the tier cannot be found or deleted.
 * @since 1.1.0
 */
function delete_price_tier( int $price ): void {
	if ( $price < 0 ) {
		throw new \InvalidArgumentException( 'Outlet price tier must not be negative.' );
	}
	$term = get_term_by( 'slug', 'outlet-price-' . $price, OUTLET_SEARCH_INDEX_TAXONOMY );
	if ( ! $term ) {
		throw new \RuntimeException( 'Outlet price tier could not be found.' );
	}
	$result = wp_delete_term( $term->term_id, OUTLET_SEARCH_INDEX_TAXONOMY );
	if ( true !== $result ) {
		throw new \RuntimeException( 'Outlet price tier could not be deleted.' );
	}
}

/**
 * Get the outlet price tiers in numerical order.
 *
 * @return int[] Price tier amounts
 * @throws \RuntimeException If the price tiers cannot be retrieved.
 * @since 1.1.0
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
 * Batch additions, updates, and deletions of outlet discount tiers.
 *
 * Validates all operations before writing and requests one reindex afterward.
 *
 * @param int[]           $add Discount percentages to add.
 * @param array<int, int> $update Existing percentages mapped to new percentages.
 * @param int[]           $delete Discount percentages to delete.
 * @throws \InvalidArgumentException If an amount is not an integer from zero to 100.
 * @throws \RuntimeException If a tier operation is invalid or fails.
 * @since 1.1.0
 */
function batch_discount_tiers( array $add = array(), array $update = array(), array $delete = array() ): void {
	if ( empty( $add ) && empty( $update ) && empty( $delete ) ) {
		return;
	}
	validate_tier_batch( $add, $update, $delete, get_discount_tiers(), 100 );
	apply_tier_batch( $add, $update, $delete, 'OutletPro\add_discount_tier', 'OutletPro\update_discount_tier', 'OutletPro\delete_discount_tier' );
}

/**
 * Add an outlet discount tier.
 *
 * @param int $discount Discount percentage between zero and 100.
 * @throws \InvalidArgumentException If the discount is outside the percentage range.
 * @throws \RuntimeException If the tier cannot be added, including duplicates.
 * @since 1.1.0
 */
function add_discount_tier( int $discount ): void {
	if ( $discount < 0 ) {
		throw new \InvalidArgumentException( 'Outlet discount tier must not be negative.' );
	}
	if ( $discount > 100 ) {
		throw new \InvalidArgumentException( 'Outlet discount tier must not exceed 100.' );
	}
	$slug = 'outlet-discount-' . $discount;
	if ( get_term_by( 'slug', $slug, OUTLET_SEARCH_INDEX_TAXONOMY ) ) {
		throw new \RuntimeException( 'Outlet discount tier could not be added.' );
	}
	$result = wp_insert_term( $slug, OUTLET_SEARCH_INDEX_TAXONOMY );
	if ( is_wp_error( $result ) ) {
		throw new \RuntimeException( 'Outlet discount tier could not be added.' );
	}
}

/**
 * Update an outlet discount tier, preserving its product assignments.
 *
 * @param int $discount Existing discount percentage.
 * @param int $new_discount Replacement discount percentage between zero and 100.
 * @throws \InvalidArgumentException If either discount is outside the percentage range.
 * @throws \RuntimeException If the tier cannot be found or updated.
 * @since 1.1.0
 */
function update_discount_tier( int $discount, int $new_discount ): void {
	if ( $discount < 0 ) {
		throw new \InvalidArgumentException( 'Outlet discount tier must not be negative.' );
	}
	if ( $discount > 100 ) {
		throw new \InvalidArgumentException( 'Outlet discount tier must not exceed 100.' );
	}
	if ( $new_discount < 0 ) {
		throw new \InvalidArgumentException( 'Outlet discount tier must not be negative.' );
	}
	if ( $new_discount > 100 ) {
		throw new \InvalidArgumentException( 'Outlet discount tier must not exceed 100.' );
	}
	$term = get_term_by( 'slug', 'outlet-discount-' . $discount, OUTLET_SEARCH_INDEX_TAXONOMY );
	if ( ! $term ) {
		throw new \RuntimeException( 'Outlet discount tier could not be found.' );
	}
	$slug   = 'outlet-discount-' . $new_discount;
	$result = wp_update_term(
		$term->term_id,
		OUTLET_SEARCH_INDEX_TAXONOMY,
		array(
			'name' => $slug,
			'slug' => $slug,
		)
	);
	if ( is_wp_error( $result ) ) {
		throw new \RuntimeException( 'Outlet discount tier could not be updated.' );
	}
}

/**
 * Delete an outlet discount tier and its product assignments.
 *
 * @param int $discount Discount percentage to delete.
 * @throws \InvalidArgumentException If the discount is outside the percentage range.
 * @throws \RuntimeException If the tier cannot be found or deleted.
 * @since 1.1.0
 */
function delete_discount_tier( int $discount ): void {
	if ( $discount < 0 ) {
		throw new \InvalidArgumentException( 'Outlet discount tier must not be negative.' );
	}
	if ( $discount > 100 ) {
		throw new \InvalidArgumentException( 'Outlet discount tier must not exceed 100.' );
	}
	$term = get_term_by( 'slug', 'outlet-discount-' . $discount, OUTLET_SEARCH_INDEX_TAXONOMY );
	if ( ! $term ) {
		throw new \RuntimeException( 'Outlet discount tier could not be found.' );
	}
	$result = wp_delete_term( $term->term_id, OUTLET_SEARCH_INDEX_TAXONOMY );
	if ( true !== $result ) {
		throw new \RuntimeException( 'Outlet discount tier could not be deleted.' );
	}
}

/**
 * Get the outlet discount tiers in numerical order.
 *
 * @return int[] Discount tier amounts
 * @throws \RuntimeException If the discount tiers cannot be retrieved.
 * @since 1.1.0
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

/**
 * Validate amounts and simulate a tier batch in execution order.
 *
 * @param int[]           $add Amounts to add.
 * @param array<int, int> $update Existing amounts mapped to new amounts.
 * @param int[]           $delete Amounts to delete.
 * @param int[]           $existing Currently configured tier amounts.
 * @param int|null        $maximum Largest permitted amount, or null for no limit.
 * @throws \InvalidArgumentException If an amount is invalid.
 * @throws \RuntimeException If an operation would fail against the tier set.
 * @internal
 */
function validate_tier_batch( array $add, array $update, array $delete, array $existing, ?int $maximum = null ): void {
	$amounts = array_merge( $add, array_keys( $update ), array_values( $update ), $delete );
	foreach ( $amounts as $amount ) {
		if ( ! is_int( $amount ) ) {
			throw new \InvalidArgumentException( 'Outlet tier amount must be an integer.' );
		}
		if ( $amount < 0 ) {
			throw new \InvalidArgumentException( 'Outlet tier amount must not be negative.' );
		}
		if ( null !== $maximum && $amount > $maximum ) {
			throw new \InvalidArgumentException( 'Outlet tier amount exceeds its maximum.' );
		}
	}
	foreach ( $add as $amount ) {
		if ( in_array( $amount, $existing, true ) ) {
			throw new \RuntimeException( 'Outlet tier could not be added.' );
		}
		$existing[] = $amount;
	}
	foreach ( $update as $amount => $new_amount ) {
		$key = array_search( $amount, $existing, true );
		if ( false === $key ) {
			throw new \RuntimeException( 'Outlet tier could not be found.' );
		}
		if ( $amount !== $new_amount && in_array( $new_amount, $existing, true ) ) {
			throw new \RuntimeException( 'Outlet tier could not be updated.' );
		}
		$existing[ $key ] = $new_amount;
	}
	foreach ( $delete as $amount ) {
		$key = array_search( $amount, $existing, true );
		if ( false === $key ) {
			throw new \RuntimeException( 'Outlet tier could not be found.' );
		}
		unset( $existing[ $key ] );
	}
}

/**
 * Apply a validated tier batch while preserving reindex hook registrations.
 *
 * @param int[]           $add Amounts to add.
 * @param array<int, int> $update Existing amounts mapped to new amounts.
 * @param int[]           $delete Amounts to delete.
 * @param callable        $add_tier Function to add one tier.
 * @param callable        $update_tier Function to update one tier.
 * @param callable        $delete_tier Function to delete one tier.
 * @throws \Throwable If a tier operation fails.
 * @internal
 */
function apply_tier_batch( array $add, array $update, array $delete, callable $add_tier, callable $update_tier, callable $delete_tier ): void {
	$callback = 'OutletPro\reindex_all_outlet_products_hook';
	$hooks    = array();
	foreach ( array( 'created_', 'edited_', 'delete_' ) as $event ) {
		$hook       = $event . OUTLET_SEARCH_INDEX_TAXONOMY;
		$priorities = array();
		$priority   = has_action( $hook, $callback );
		while ( false !== $priority ) {
			$priorities[] = $priority;
			remove_action( $hook, $callback, $priority );
			$priority = has_action( $hook, $callback );
		}
		$hooks[ $hook ] = array(
			'priorities' => $priorities,
			'count'      => did_action( $hook ),
		);
	}

	try {
		foreach ( $add as $amount ) {
			$add_tier( $amount );
		}
		foreach ( $update as $amount => $new_amount ) {
			$update_tier( $amount, $new_amount );
		}
		foreach ( $delete as $amount ) {
			$delete_tier( $amount );
		}
	} finally {
		$changed = false;
		foreach ( $hooks as $hook => $state ) {
			foreach ( $state['priorities'] as $priority ) {
				add_action( $hook, $callback, $priority );
			}
			if ( empty( $state['priorities'] ) ) {
				continue;
			}
			if ( did_action( $hook ) <= $state['count'] ) {
				continue;
			}
			$changed = true;
		}
		if ( $changed ) {
			reindex_all_outlet_products_hook();
		}
	}
}
