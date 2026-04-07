<?php
/**
 * Shortcode-related functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize shortcodes.
 *
 * @internal
 */
function init_shortcodes(): void {
	add_filter( 'woocommerce_shortcode_products_query', 'WC_Clearance\filter_products_shortcode_query_hook', 10, 3 );
	add_filter( 'shortcode_atts_products', 'WC_Clearance\add_products_shortcode_attribute_hook', 10, 3 );
}

/**
 * Filter the [products] shortcode query args to include only clearance products when wc_clearance is set.
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

	if ( empty( $attributes['wc_clearance'] ) ) {
		return $query_args;
	}

	if ( ! \wc_string_to_bool( $attributes['wc_clearance'] ) ) {
		return $query_args;
	}

	if ( empty( $query_args['tax_query'] ) || ! is_array( $query_args['tax_query'] ) ) {
		$query_args['tax_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	}

	$query_args['tax_query'][] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		'taxonomy' => CLEARANCE_STATUS_TAXONOMY,
		'field'    => 'name',
		'terms'    => CLEARANCE_STATUS_CANONICAL_TERM,
	);

	return $query_args;
}

/**
 * Register the wc_clearance attribute for the [products] shortcode.
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

	if ( isset( $atts['wc_clearance'] ) ) {
		$out['wc_clearance'] = \wc_string_to_bool( $atts['wc_clearance'] );
	}

	return $out;
}
