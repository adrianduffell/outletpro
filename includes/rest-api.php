<?php
/**
 * REST API integration functions.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Product membership schema shared by the three v3 routes.
 *
 * @internal
 */
const OUTLET_PRODUCT_REST_FIELD = array(
	'type'       => 'object',
	'context'    => array( 'view', 'edit' ),
	'properties' => array( 'is_outlet' => array( 'type' => 'boolean' ) ),
);

/**
 * Helper to initialize REST API integrations.
 *
 * @internal
 */
function init_rest_api(): void {
	add_filter( 'rest_endpoints', 'OutletPro\extend_outletpro_product_collection_route_hook' );
	add_filter( 'rest_endpoints', 'OutletPro\extend_outletpro_product_route_hook' );
	add_filter( 'rest_endpoints', 'OutletPro\extend_outletpro_product_batch_route_hook' );
	add_filter( 'woocommerce_rest_prepare_product_object', 'OutletPro\append_outletpro_product_field_hook', 10, 3 );
	add_filter( 'woocommerce_rest_pre_insert_product_object', 'OutletPro\validate_outletpro_product_hook', 10, 2 );
	add_action( 'woocommerce_rest_insert_product_object', 'OutletPro\save_outletpro_product_field_hook', 10, 2 );
	add_filter( 'rest_product_collection_params', 'OutletPro\add_outletpro_rest_param_hook' );
	add_filter( 'woocommerce_rest_product_object_query', 'OutletPro\handle_outletpro_rest_param', 10, 2 );
	add_filter( 'rest_product_query', 'OutletPro\handle_outletpro_rest_param', 10, 2 );
}

/**
 * Add `outletpro` parameter to the products REST API collection params.
 *
 * @internal WordPress filter hook
 * @param array<string, mixed> $params Existing collection parameters.
 * @return array<string, mixed> Modified collection parameters.
 */
function add_outletpro_rest_param_hook( array $params ): array {
	$params['outletpro'] = array(
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
function handle_outletpro_rest_param( array $args, \WP_REST_Request $request ): array {
	if ( empty( $request['outletpro'] ) ) {
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

/**
 * Identify the product routes supporting membership, including internal batch requests.
 *
 * @param \WP_REST_Request $request REST request.
 * @internal
 */
function is_outletpro_product_request( \WP_REST_Request $request ): bool {
	if ( ! in_array( $request->get_method(), array( 'GET', 'POST', 'PUT', 'PATCH' ), true ) ) {
		return false;
	}
	return 1 === preg_match( '#^/wc/v3/products(?:/[0-9]+|/batch)?/?$#', $request->get_route() );
}

/**
 * Validate membership without coercing strings, numbers, or null to booleans.
 *
 * @param mixed $value Product extension data.
 * @internal
 */
function validate_outletpro_product_field( $value ): bool { // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint -- PHP 7.4 has no mixed type.
	if ( ! is_array( $value ) ) {
		return false;
	}
	if ( array() !== $value && wp_is_numeric_array( $value ) ) {
		return false;
	}
	if ( ! array_key_exists( 'is_outlet', $value ) ) {
		return true;
	}
	return is_bool( $value['is_outlet'] );
}

/**
 * Extend the v3 product collection schema and creation arguments.
 *
 * Fired by rest_endpoints.
 *
 * @param array<string, array<mixed>> $routes Registered routes.
 * @return array<string, array<mixed>> Extended routes.
 * @internal WordPress filter hook
 */
function extend_outletpro_product_collection_route_hook( array $routes ): array {
	$route = '/wc/v3/products';
	if ( ! isset( $routes[ $route ] ) ) {
		return $routes;
	}
	$schema                                   = $routes[ $route ]['schema'];
	$routes[ $route ]['schema']               = static fn(): array => array_replace_recursive( $schema(), array( 'properties' => array( 'outletpro' => OUTLET_PRODUCT_REST_FIELD ) ) );
	$routes[ $route ][1]['args']['outletpro'] = OUTLET_PRODUCT_REST_FIELD + array( 'validate_callback' => 'OutletPro\validate_outletpro_product_field' );
	return $routes;
}

/**
 * Extend the v3 individual product schema and update arguments.
 *
 * Fired by rest_endpoints.
 *
 * @param array<string, array<mixed>> $routes Registered routes.
 * @return array<string, array<mixed>> Extended routes.
 * @internal WordPress filter hook
 */
function extend_outletpro_product_route_hook( array $routes ): array {
	$route = '/wc/v3/products/(?P<id>[\d]+)';
	if ( ! isset( $routes[ $route ] ) ) {
		return $routes;
	}
	$schema                                   = $routes[ $route ]['schema'];
	$routes[ $route ]['schema']               = static fn(): array => array_replace_recursive( $schema(), array( 'properties' => array( 'outletpro' => OUTLET_PRODUCT_REST_FIELD ) ) );
	$routes[ $route ][1]['args']['outletpro'] = OUTLET_PRODUCT_REST_FIELD + array( 'validate_callback' => 'OutletPro\validate_outletpro_product_field' );
	return $routes;
}

/**
 * Extend the v3 batch schema while retaining per-item validation.
 *
 * Fired by rest_endpoints.
 *
 * @param array<string, array<mixed>> $routes Registered routes.
 * @return array<string, array<mixed>> Extended routes.
 * @internal WordPress filter hook
 */
function extend_outletpro_product_batch_route_hook( array $routes ): array {
	$route = '/wc/v3/products/batch';
	if ( ! isset( $routes[ $route ] ) ) {
		return $routes;
	}
	$schema                     = $routes[ $route ]['schema'];
	$routes[ $route ]['schema'] = static fn(): array => array_replace_recursive(
		$schema(),
		array(
			'properties' => array(
				'create' => array( 'items' => array( 'properties' => array( 'outletpro' => OUTLET_PRODUCT_REST_FIELD ) ) ),
				'update' => array( 'items' => array( 'properties' => array( 'outletpro' => OUTLET_PRODUCT_REST_FIELD ) ) ),
			),
		)
	);
	return $routes;
}

/**
 * Append membership when the requested product fields include it.
 *
 * Fired by woocommerce_rest_prepare_product_object.
 *
 * @param \WP_REST_Response $response Product response.
 * @param \WC_Product       $product Product being returned.
 * @param \WP_REST_Request  $request REST request.
 * @internal WooCommerce filter hook
 */
function append_outletpro_product_field_hook( \WP_REST_Response $response, \WC_Product $product, \WP_REST_Request $request ): \WP_REST_Response {
	if ( ! is_outletpro_product_request( $request ) ) {
		return $response;
	}
	$fields = array_map( 'trim', wp_parse_list( $request['_fields'] ?? array() ) );
	if ( $fields ) {
		if ( ! rest_is_field_included( 'outletpro.is_outlet', $fields ) ) {
			return $response;
		}
	}
	$data = $response->get_data();
	try {
		$data['outletpro']['is_outlet'] = is_outlet( $product );
	} catch ( \Throwable $e ) {
		wc_get_logger()->error( 'Outlet product membership could not be retrieved.' );
		return $response;
	}
	$response->set_data( $data );
	return $response;
}

/**
 * Validate each write before WooCommerce saves any product changes.
 *
 * Fired by woocommerce_rest_pre_insert_product_object.
 *
 * @param \WC_Product|\WP_Error $product Prepared product or upstream error.
 * @param \WP_REST_Request      $request REST request, including internal batch items.
 * @return \WC_Product|\WP_Error Product or validation error.
 * @internal WooCommerce filter hook
 */
function validate_outletpro_product_hook( $product, \WP_REST_Request $request ) { // phpcs:ignore SlevomatCodingStandard.TypeHints -- PHP 7.4 cannot express the WC_Product|WP_Error union.
	if ( is_wp_error( $product ) ) {
		return $product;
	}
	if ( ! is_outletpro_product_request( $request ) ) {
		return $product;
	}
	if ( ! $request->has_param( 'outletpro' ) ) {
		return $product;
	}
	if ( ! validate_outletpro_product_field( $request['outletpro'] ) ) {
		return new \WP_Error( 'outletpro_invalid_membership', __( 'Outlet membership must be a boolean in an outletpro object.', 'outletpro' ), array( 'status' => 400 ) );
	}
	if ( ! array_key_exists( 'is_outlet', $request['outletpro'] ) ) {
		return $product;
	}
	if ( $product->is_type( 'variation' ) ) {
		return new \WP_Error( 'outletpro_invalid_product', __( 'Change outlet membership on the parent product.', 'outletpro' ), array( 'status' => 400 ) );
	}
	if ( 'trash' === $product->get_status() ) {
		return new \WP_Error( 'outletpro_invalid_product', __( 'Restore the product before changing outlet membership.', 'outletpro' ), array( 'status' => 400 ) );
	}
	return $product;
}

/**
 * Persist membership after the product has an ID, including native batch items.
 *
 * Fired by woocommerce_rest_insert_product_object.
 *
 * @param \WC_Product      $product Saved product.
 * @param \WP_REST_Request $request Validated REST request.
 * @throws \WC_REST_Exception If membership storage fails; WooCommerce reports this per batch item.
 * @internal WooCommerce action hook
 */
function save_outletpro_product_field_hook( \WC_Product $product, \WP_REST_Request $request ): void {
	if ( ! is_outletpro_product_request( $request ) ) {
		return;
	}
	if ( ! $request->has_param( 'outletpro' ) ) {
		return;
	}
	if ( ! array_key_exists( 'is_outlet', $request['outletpro'] ) ) {
		return;
	}
	try {
		set_outlet( $product, $request['outletpro']['is_outlet'] );
	} catch ( \Throwable $e ) {
		wc_get_logger()->error( 'Outlet product membership could not be updated.' );
		throw new \WC_REST_Exception( 'outletpro_membership_failed', __( 'Outlet product membership could not be updated.', 'outletpro' ), 500 ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- REST errors are JSON, not HTML.
	}
}
