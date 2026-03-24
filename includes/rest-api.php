<?php
/**
 * REST API integration functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize REST API integrations.
 *
 * @since 1.0.0
 */
function init_rest_api(): void {
	add_filter( 'rest_product_collection_params', 'WC_Clearance\rest_product_collection_params_hook' );
	add_filter( 'woocommerce_rest_product_object_query', 'WC_Clearance\woocommerce_rest_product_object_query_hook', 10, 2 );
}

/**
 * Add `wc_clearance` parameter to the products REST API collection params.
 *
 * @internal WordPress filter hook
 * @param array<string, mixed> $params Existing collection parameters.
 * @return array<string, mixed> Modified collection parameters.
 */
function rest_product_collection_params_hook( array $params ): array {
	$params['wc_clearance'] = array(
		'description'       => __( 'Limit results to products in the clearance section.', 'wc-clearance' ),
		'type'              => 'boolean',
		'sanitize_callback' => 'rest_sanitize_boolean',
		'validate_callback' => 'rest_validate_request_arg',
	);

	return $params;
}

/**
 * Filter the products REST API query to include only clearance products when requested.
 *
 * @internal WooCommerce filter hook
 * @param array<string, mixed> $args    WP_Query arguments.
 * @param \WP_REST_Request     $request REST API request.
 * @return array<string, mixed> Modified WP_Query arguments.
 */
function woocommerce_rest_product_object_query_hook( array $args, \WP_REST_Request $request ): array {
	if ( empty( $request['wc_clearance'] ) ) {
		return $args;
	}

	if ( empty( $args['tax_query'] ) || ! is_array( $args['tax_query'] ) ) {
		$args['tax_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	}

	$args['tax_query'][] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		'taxonomy' => CLEARANCE_STATUS_TAXONOMY,
		'field'    => 'slug',
		'terms'    => array( CLEARANCE_STATUS_CANONICAL_TERM ),
	);

	return $args;
}
