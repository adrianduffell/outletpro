<?php
/**
 * Shortcode-related functions.
 *
 * @package WC_Outlet
 */

namespace WC_Outlet;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize shortcodes.
 *
 * @internal
 */
function init_shortcodes(): void {
	add_filter( 'woocommerce_shortcode_products_query', 'WC_Outlet\filter_products_shortcode_query_hook', 10, 3 );
	add_filter( 'shortcode_atts_products', 'WC_Outlet\add_products_shortcode_attribute_hook', 10, 3 );
	add_filter( 'posts_clauses', 'WC_Outlet\max_price_posts_clauses', 10, 2 );
}

/**
 * Filter the [products] shortcode query args to include only outlet products when wc_outlet is set.
 *
 * Fired by `woocommerce_shortcode_products_query`.
 *
 * @param array<string, mixed> $query_args   The WP_Query arguments.
 * @param array<string, mixed> $attributes   The shortcode attributes.
 * @param string               $unused_type  The shortcode type (unused; this filter is specific to [products]).
 * @return array<string, mixed> The modified WP_Query arguments.
 * @internal WordPress filter
 */
function filter_products_shortcode_query_hook( array $query_args, array $attributes, string $unused_type ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

	if ( empty( $attributes['wc_outlet'] ) ) {
		return $query_args;
	}

	if ( ! \wc_string_to_bool( $attributes['wc_outlet'] ) ) {
		return $query_args;
	}

	// Flag this query for max price filtering.
	if ( isset( $_GET['max_price'] ) ) {
		$query_args['wc_outlet_max_price'] = sanitize_unsigned_integer( $_GET['max_price'] );
	}

	if ( empty( $query_args['tax_query'] ) || ! is_array( $query_args['tax_query'] ) ) {
		$query_args['tax_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	}

	$query_args['tax_query'][] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		'taxonomy' => OUTLET_STATUS_TAXONOMY,
		'field'    => 'name',
		'terms'    => OUTLET_STATUS_CANONICAL_TERM,
	);

	return $query_args;
}

/**
 * Register the wc_outlet attribute for the [products] shortcode.
 *
 * Fired by `shortcode_atts_products`.
 *
 * @param array<string, mixed> $out           The output array of shortcode attributes.
 * @param array<string, mixed> $unused_pairs  The supported attributes and their defaults (unused in this implementation).
 * @param array<string, mixed> $atts          The user-defined shortcode attributes.
 * @return array<string, mixed> The modified output array of shortcode attributes.
 * @internal WordPress filter
 */
function add_products_shortcode_attribute_hook( array $out, array $unused_pairs, array $atts ): array {

	if ( isset( $atts['wc_outlet'] ) ) {
		$out['wc_outlet'] = \wc_string_to_bool( $atts['wc_outlet'] );
	}

	return $out;
}

/**
 * Add a max price SQL clause for outlet-marked shortcode product queries.
 *
 * Fired by `posts_clauses`.
 *
 * @param array<string, string> $clauses The SQL clauses for the current query.
 * @param \WP_Query             $query   The current query instance.
 * @return array<string, string> The modified SQL clauses.
 * @internal WordPress filter
 */
function max_price_posts_clauses( array $clauses, \WP_Query $query ): array {
	global $wpdb;

	// Bail if the wc_outlet_max_price var is not set.
	if ( is_null( $query->get( 'wc_outlet_max_price', null ) ) ) {
		return $clauses;
	}

	$max_price = sanitize_unsigned_integer( $query->get( 'wc_outlet_max_price' ) );

	if ( is_null( $max_price ) ) {
		return $clauses;
	}

	if ( false === strpos( $clauses['join'], 'wc_product_meta_lookup' ) ) {
		$clauses['join'] .= " LEFT JOIN {$wpdb->wc_product_meta_lookup} wc_product_meta_lookup ON {$wpdb->posts}.ID = wc_product_meta_lookup.product_id ";
	}

	$clauses['where'] .= $wpdb->prepare(
		' AND NOT ( %d < wc_product_meta_lookup.min_price ) ',
		$max_price
	);

	return $clauses;
}
