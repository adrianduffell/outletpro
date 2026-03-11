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
 * @since 1.0.0
 */
function init_shortcodes(): void {
	add_filter( 'woocommerce_shortcode_products_query', __NAMESPACE__ . '\hook_filter_products_shortcode_query', 10, 3 );
	add_filter( 'shortcode_atts_products', __NAMESPACE__ . '\hook_add_products_shortcode_attribute', 10, 3 );
}

/**
 * Filter the [products] shortcode query args to include only clearance products when is_clearance is set.
 *
 * @param array  $query_args   The WP_Query arguments.
 * @param array  $attributes   The shortcode attributes.
 * @param string $unused_type  The shortcode type (unused; this filter is specific to [products]).
 * @return array The modified WP_Query arguments.
 * @since 1.0.0
 */
function hook_filter_products_shortcode_query( array $query_args, array $attributes, string $unused_type ): array {

	if ( empty( $attributes['is_clearance'] ) ) {
		return $query_args;
	}

	if ( ! \wc_string_to_bool( $attributes['is_clearance'] ) ) {
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
 * Register the is_clearance attribute for the [products] shortcode.
 *
 * @param array $out           The output array of shortcode attributes.
 * @param array $unused_pairs  The supported attributes and their defaults (unused in this implementation).
 * @param array $atts          The user-defined shortcode attributes.
 * @return array The modified output array of shortcode attributes.
 * @since 1.0.0
 */
function hook_add_products_shortcode_attribute( array $out, array $unused_pairs, array $atts ): array {

	if ( isset( $atts['is_clearance'] ) ) {
		$out['is_clearance'] = \wc_string_to_bool( $atts['is_clearance'] );
	}

	return $out;
}
