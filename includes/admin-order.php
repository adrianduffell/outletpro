<?php
/**
 * Admin order functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Order item meta key used to store the clearance status at time of purchase.
 *
 * @since 1.0.0
 */
const ORDER_ITEM_CLEARANCE_META_KEY = '_wc_clearance';

/**
 * Helper to initialize order creation hooks.
 *
 * @internal
 */
function init_order(): void {
	add_action( 'woocommerce_checkout_create_order_line_item', 'WC_Clearance\flag_order_line_item_clearance_hook', 10, 4 );
}

/**
 * Helper to initialize admin order display hooks.
 *
 * @internal
 */
function init_admin_order(): void {
	add_action( 'woocommerce_after_order_itemmeta', 'WC_Clearance\display_order_item_clearance_badge_hook', 10, 3 );
}

/**
 * Flag the order line item with clearance status at time of purchase.
 *
 * Fired by `woocommerce_checkout_create_order_line_item`.
 *
 * @param \WC_Order_Item_Product $item           Order item being created.
 * @param string                 $_cart_item_key  Cart item key (unused).
 * @param array<string, mixed>   $values          Cart item data.
 * @param \WC_Order              $_order          The order being created (unused).
 * @internal WordPress action hook
 */
function flag_order_line_item_clearance_hook( \WC_Order_Item_Product $item, string $_cart_item_key, array $values, \WC_Order $_order ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	$product_id = isset( $values['product_id'] ) ? (int) $values['product_id'] : 0;

	if ( ! $product_id ) {
		return;
	}

	$product = wc_get_product( $product_id );

	if ( ! $product instanceof \WC_Product ) {
		return;
	}

	try {
		if ( ! is_clearance( $product ) ) {
			return;
		}
	} catch ( \Throwable $e ) {
		return;
	}

	$item->add_meta_data( ORDER_ITEM_CLEARANCE_META_KEY, 'yes', true );
}

/**
 * Display clearance badge text on the admin order screen.
 *
 * Fired by `woocommerce_after_order_itemmeta`.
 *
 * @param int            $_item_id The order item ID (unused).
 * @param \WC_Order_Item $item     The order item object.
 * @param mixed          $_product The product (unused).
 * @internal WordPress action hook
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
 */
function display_order_item_clearance_badge_hook( $_item_id, \WC_Order_Item $item, $_product ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	if ( 'yes' !== $item->get_meta( ORDER_ITEM_CLEARANCE_META_KEY ) ) {
		return;
	}

	$label = get_option( CLEARANCE_BADGE_LABEL_OPTION );

	if ( ! is_string( $label ) || '' === $label ) {
		$label = __( 'Clearance', 'wc-clearance' );
	}

	echo esc_html( $label );
}
