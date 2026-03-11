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
	add_filter( 'woocommerce_shortcode_products_query', __NAMESPACE__ . '\filter_products_shortcode_query', 10, 3 );
	add_filter( 'shortcode_atts_products', __NAMESPACE__ . '\add_products_shortcode_attribute', 10, 3 );
}

/**
 * Filter the [products] shortcode query args to include only clearance products when on_clearance is set.
 *
 * @param array  $query_args The WP_Query arguments.
 * @param array  $attributes The shortcode attributes.
 * @param string $type       The shortcode type.
 * @return array The modified WP_Query arguments.
 * @since 1.0.0
 */
function filter_products_shortcode_query( array $query_args, array $attributes, string $type ): array {

	if ( empty( $attributes['on_clearance'] ) ) {
		return $query_args;
	}

	if ( ! \wc_string_to_bool( $attributes['on_clearance'] ) ) {
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
 * Register the on_clearance attribute for the [products] shortcode.
 *
 * @param array $out           The output array of shortcode attributes.
 * @param array $unused_pairs  The supported attributes and their defaults (unused in this implementation).
 * @param array $atts          The user-defined shortcode attributes.
 * @return array The modified output array of shortcode attributes.
 * @since 1.0.0
 */
function add_products_shortcode_attribute( array $out, array $unused_pairs, array $atts ): array {

	if ( isset( $atts['on_clearance'] ) ) {
		$out['on_clearance'] = \wc_string_to_bool( $atts['on_clearance'] );
	}

	return $out;
}
