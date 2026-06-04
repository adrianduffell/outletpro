<?php
/**
 * Order functions.
 *
 * @package OutletPro
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize order hooks.
 *
 * @internal
 */
function init_orders(): void {
	add_action( 'woocommerce_new_order_item', 'OutletPro\flag_order_item_outlet_hook', 10, 3 );
}

/**
 * Flag the order item with outlet status at time of purchase.
 *
 * Fired by `woocommerce_new_order_item`.
 *
 * @param int            $item_id   The order item ID.
 * @param \WC_Order_Item $item      The order item being added.
 * @param int            $_order_id The order ID (unused).
 * @internal WordPress action hook
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
 */
function flag_order_item_outlet_hook( $item_id, \WC_Order_Item $item, $_order_id ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	if ( ! $item instanceof \WC_Order_Item_Product ) {
		return;
	}

	$product_id = $item->get_product_id();

	if ( ! $product_id ) {
		return;
	}

	$product = wc_get_product( $product_id );

	if ( ! $product instanceof \WC_Product ) {
		return;
	}

	try {
		if ( ! is_outlet( $product ) ) {
			return;
		}
	} catch ( \Throwable $e ) {
		return;
	}

	wc_add_order_item_meta( $item_id, ORDER_ITEM_OUTLET_META_KEY, 'yes', true );
}
