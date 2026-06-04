<?php
/**
 * REST API integration functions.
 *
 * @package OutletPro
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize REST API integrations.
 *
 * @internal
 */
function init_rest_api(): void {
	add_filter( 'rest_product_collection_params', 'OutletPro\add_wc_outlet_rest_param_hook' );
	add_filter( 'woocommerce_rest_product_object_query', 'OutletPro\handle_wc_outlet_rest_param', 10, 2 );
	add_filter( 'rest_product_query', 'OutletPro\handle_wc_outlet_rest_param', 10, 2 );
}

/**
 * Add `wc_outlet` parameter to the products REST API collection params.
 *
 * @internal WordPress filter hook
 * @param array<string, mixed> $params Existing collection parameters.
 * @return array<string, mixed> Modified collection parameters.
 */
function add_wc_outlet_rest_param_hook( array $params ): array {
	$params['wc_outlet'] = array(
		'description'       => __( 'Limit results to outlet products.', 'outletpro' ),
		'type'              => 'boolean',
		'sanitize_callback' => 'rest_sanitize_boolean',
		'validate_callback' => 'rest_validate_request_arg',
	);

	return $params;
}

/**
 * Filter the products REST API query to include only outlet products when requested.
 *
 * @internal WooCommerce filter hook, WordPress filter hook
 * @param array<string, mixed> $args    WP_Query arguments.
 * @param \WP_REST_Request     $request REST API request.
 * @return array<string, mixed> Modified WP_Query arguments.
 */
function handle_wc_outlet_rest_param( array $args, \WP_REST_Request $request ): array {
	if ( empty( $request['wc_outlet'] ) ) {
		return $args;
	}

	if ( empty( $args['tax_query'] ) || ! is_array( $args['tax_query'] ) ) {
		$args['tax_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	}

	$args['tax_query'][] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		'taxonomy' => OUTLET_STATUS_TAXONOMY,
		'field'    => 'slug',
		'terms'    => array( OUTLET_STATUS_CANONICAL_TERM ),
	);

	return $args;
}
