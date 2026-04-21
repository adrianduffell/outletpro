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
 * Helper to de-initialize cart integrations back to the uninitialized state.
 *
 * @internal
 */
function deinit_cart(): void {
	remove_filter( 'woocommerce_get_item_data', 'WC_Clearance\add_clearance_to_cart_item_meta_hook', PHP_INT_MAX );
}

/**
 * Adds clearance status into the cart item meta.
 *
 * Fired by `woocommerce_get_item_data`.
 *
 * @param array $item_data The existing cart item data.
 * @param array $cart_item The cart item.
 * @return array<int, array<string, mixed>>
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

	$clearance_label = get_option( CLEARANCE_BADGE_LABEL_OPTION );

	if ( ! is_string( $clearance_label ) || '' === trim( $clearance_label ) ) {
		$clearance_label = __( 'Clearance', 'wc-clearance' );
	}

	// Important: The CSS badge replacement expects clearance meta to be first.
	array_unshift(
		$item_data,
		array(
			'key'   => $clearance_label,
			'value' => __( 'Yes', 'wc-clearance' ),
			'display' => sprintf(
			'<span class="wc-clearance-cart-item-meta">%s</span>',
				esc_html__( 'Yes', 'wc-clearance' )
			),
		)
	);

	return $item_data;
}
