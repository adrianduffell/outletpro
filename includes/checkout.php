<?php
/**
 * Checkout functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize checkout hooks.
 *
 * @internal
 */
function init_checkout(): void {
	add_action( 'woocommerce_checkout_create_order_line_item', 'WC_Clearance\flag_order_line_item_clearance_hook', 10, 4 );
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
