<?php
/**
 * Cart functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize cart integrations.
 *
 * @internal
 */
function init_cart(): void {
	add_filter( 'woocommerce_get_item_data', 'WC_Clearance\add_clearance_to_cart_item_meta_hook', PHP_INT_MAX, 2 );
}

/**
 * Adds clearance status into the cart item meta.
 *
 * Fired by `woocommerce_get_item_data`.
 *
 * @param array $item_data The existing cart item data.
 * @param array $cart_item The cart item.
 * @return array<int, array{key: string, value: string}>
 * @internal WordPress filter hook
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
 */
function add_clearance_to_cart_item_meta_hook( $item_data, $cart_item ): array {
	$product = $cart_item['data'] ?? null;

	if ( ! $product instanceof \WC_Product ) {
		return $item_data;
	}

	try {
		if ( ! is_clearance( $product ) ) {
			return $item_data;
		}
	} catch ( \Throwable $e ) {
		return $item_data;
	}

	$item_data[] = array(
		'key'   => get_option( CLEARANCE_BADGE_LABEL_OPTION, __( 'Clearance', 'wc-clearance' ) ),
		'value' => __( 'Yes', 'wc-clearance' ),
	);

	return $item_data;
}
