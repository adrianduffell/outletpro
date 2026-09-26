<?php
/**
 * Store API integration functions.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize Store API integrations.
 *
 * @internal
 */
function init_store_api(): void {
	register_store_api_cart_item_endpoint_data();
}

/**
 * Registers custom data for the wc/store/cart/items store-api endpoint.
 *
 * @internal
 */
function register_store_api_cart_item_endpoint_data(): void {
	\woocommerce_store_api_register_endpoint_data(
		array(
			'endpoint'        => \Automattic\WooCommerce\StoreApi\Schemas\V1\CartItemSchema::IDENTIFIER,
			'namespace'       => 'outletpro',
			'data_callback'   => 'OutletPro\store_api_cart_item_data',
			'schema_callback' => function (): array {
				return array(
					'is_outlet' => array(
						'description' => __( 'Whether the cart item is an outlet product.', 'outletpro' ),
						'type'        => 'boolean',
						'readonly'    => true,
					),
				);
			},
			'schema_type'     => ARRAY_A,
		)
	);
}

/**
 * Returns custom data for the wc/store/cart/items store-api endpoint.
 *
 * @param array $cart_item The cart item.
 * @return array{is_outlet: bool} Cart item extension data.
 * @internal
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
 */
function store_api_cart_item_data( $cart_item ): array {
	$product = $cart_item['data'] ?? null;

	if ( ! $product instanceof \WC_Product ) {
		return array();
	}

	try {
		$is_outlet = is_outlet( $product );
	} catch ( \Throwable $e ) {
		\wc_get_logger()->error( 'Outlet status could not be retrieved' );
		return array();
	}

	return array(
		'is_outlet' => $is_outlet,
	);
}
