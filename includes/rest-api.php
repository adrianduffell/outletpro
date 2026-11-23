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
 * Helper to initialize REST API integrations.
 *
 * @internal
 */
function init_rest_api(): void {
	add_action( 'rest_api_init', 'OutletPro\register_outletpro_rest_field_hook' );
	add_filter( 'woocommerce_rest_pre_insert_product', 'OutletPro\validate_rest_field_woocommerce_hook', 10, 2 );
	add_filter( 'woocommerce_rest_pre_insert_product_object', 'OutletPro\validate_rest_field_woocommerce_hook', 10, 2 );
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
 * Register the outletpro product field.
 *
 * Field is an object with is_outlet property.
 *
 * Fired by rest_api_init.
 *
 * @internal WordPress action hook
 */
function register_outletpro_rest_field_hook(): void {
	register_rest_field(
		'product',
		'outletpro',
		array(
			'get_callback'    => 'OutletPro\get_rest_field_callback',
			'update_callback' => 'OutletPro\update_rest_field_callback',
			'schema'          => array(
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'properties'  => array( 'is_outlet' => array( 'type' => 'boolean' ) ),
				'arg_options' => array( 'validate_callback' => 'OutletPro\validate_rest_field_callback' ),
			),
		)
	);
}

/**
 * Get outletpro REST field.
 *
 * @param array<string, mixed> $data Prepared REST product data.
 * @return array{is_outlet?: bool} Field data; is_outlet is omitted when unavailable.
 * @internal REST field callback
 */
function get_rest_field_callback( array $data ): array {
	try {
		return array( 'is_outlet' => is_outlet( wc_get_product( $data['id'] ) ) );
	} catch ( \Throwable $e ) {
		wc_get_logger()->error( 'Outlet product status could not be retrieved.' );
		return array();
	}
}

/**
 * Update outletpro REST field.
 *
 * @param array{is_outlet?: bool} $value Validated field value.
 * @param \WC_Product|\WP_Post    $product Saved product.
 * @param string                  $field Registered field name.
 * @param \WP_REST_Request        $request REST request.
 * @return bool|\WP_Error True on success or a WordPress REST error.
 * @throws \WC_REST_Exception If WooCommerce cannot update the field.
 * @internal REST field callback
 */
function update_rest_field_callback( array $value, object $product, string $field, \WP_REST_Request $request ) { // phpcs:ignore SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint -- PHP 7.4 cannot express bool|WP_Error.
	if ( ! array_key_exists( 'is_outlet', $value ) ) {
		return true;
	}
	try {
		$wc_product = $product instanceof \WC_Product ? $product : wc_get_product( $product->ID );
		set_outlet( $wc_product, $value['is_outlet'] );
		return true;
	} catch ( \Throwable $e ) {
		wc_get_logger()->error( 'Outlet product status could not be updated.' );
		if ( 0 === strpos( $request->get_route(), '/wc/' ) ) {
			// WooCommerce ignores WP_Error returned by additional-field callbacks.
			throw new \WC_REST_Exception( 'outletpro_update_failed', __( 'Outlet product status could not be updated.', 'outletpro' ), 500 ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- REST errors are JSON, not HTML.
		}
		return new \WP_Error( 'outletpro_update_failed', __( 'Outlet product status could not be updated.', 'outletpro' ), array( 'status' => 500 ) );
	}
}

/**
 * Validate outletpro REST field.
 *
 * @param mixed $value Field value.
 * @internal REST validation callback
 */
function validate_rest_field_callback( $value ): bool { // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint -- PHP 7.4 has no mixed type.
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
 * Validate outletpro REST field (WooCommerce API only).
 *
 * Fired by woocommerce_rest_pre_insert_product and woocommerce_rest_pre_insert_product_object.
 *
 * @param object           $product Prepared product or upstream error.
 * @param \WP_REST_Request $request Product request.
 * @return object Product or validation error.
 * @throws \WC_REST_Exception If a WooCommerce field is invalid.
 * @internal WooCommerce filter hook
 */
function validate_rest_field_woocommerce_hook( object $product, \WP_REST_Request $request ): object {
	if ( is_wp_error( $product ) ) {
		return $product;
	}
	if ( ! $request->has_param( 'outletpro' ) ) {
		return $product;
	}
	if ( validate_rest_field_callback( $request['outletpro'] ) ) {
		return $product;
	}
	throw new \WC_REST_Exception( 'outletpro_invalid_is_outlet', __( 'is_outlet must be a boolean.', 'outletpro' ), 400 ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- REST errors are JSON, not HTML.
}
