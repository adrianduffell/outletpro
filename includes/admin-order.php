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
 * Helper to initialize admin order display hooks.
 *
 * @internal
 */
function init_admin_order(): void {
	add_action( 'woocommerce_after_order_itemmeta', 'WC_Clearance\display_order_item_clearance_badge_hook', 10, 3 );
	add_filter( 'woocommerce_hidden_order_itemmeta', 'WC_Clearance\hide_order_item_clearance_meta_hook' );
}

/**
 * Hide the clearance meta key from the default order item meta display.
 *
 * Fired by `woocommerce_hidden_order_itemmeta`.
 *
 * @param string[] $hidden_meta_keys The list of hidden meta keys.
 * @return string[] The updated list.
 * @internal WordPress filter hook
 */
function hide_order_item_clearance_meta_hook( array $hidden_meta_keys ): array {
	$hidden_meta_keys[] = ORDER_ITEM_CLEARANCE_META_KEY;
	return $hidden_meta_keys;
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
