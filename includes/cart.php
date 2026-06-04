<?php
/**
 * Cart functions.
 *
 * @package WC_Outlet
 */

namespace WC_Outlet;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize cart integrations.
 *
 * @internal
 */
function init_cart(): void {
	add_filter( 'woocommerce_get_item_data', 'WC_Outlet\add_outlet_to_cart_item_meta_hook', PHP_INT_MAX, 2 );
}

/**
 * Helper to de-initialize cart integrations back to the uninitialized state.
 *
 * @internal
 */
function deinit_cart(): void {
	remove_filter( 'woocommerce_get_item_data', 'WC_Outlet\add_outlet_to_cart_item_meta_hook', PHP_INT_MAX );
}

/**
 * Adds outlet status into the cart item meta.
 *
 * Fired by `woocommerce_get_item_data`.
 *
 * @param array $item_data The existing cart item data.
 * @param array $cart_item The cart item.
 * @return array<int, array<string, mixed>>
 * @internal WordPress filter hook
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
 */
function add_outlet_to_cart_item_meta_hook( $item_data, $cart_item ): array {
	$product = $cart_item['data'] ?? null;

	if ( ! $product instanceof \WC_Product ) {
		return $item_data;
	}

	try {
		if ( ! is_outlet( $product ) ) {
			return $item_data;
		}
	} catch ( \Throwable $e ) {
		return $item_data;
	}

	$outlet_label = get_option( OUTLET_BADGE_LABEL_OPTION );

	if ( ! is_string( $outlet_label ) || '' === trim( $outlet_label ) ) {
		return $item_data;
	}

	// Important: The CSS badge replacement expects outlet meta to be first.
	array_unshift(
		$item_data,
		array(
			'key'     => $outlet_label,
			'value'   => __( 'Yes', 'outletpro' ),
			'display' => sprintf(
				'<span class="wc-outlet-cart-item-meta">%s</span>',
				esc_html__( 'Yes', 'outletpro' )
			),
		)
	);

	return $item_data;
}
