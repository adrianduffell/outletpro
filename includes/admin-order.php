<?php
/**
 * Admin order functions.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Order item meta key used to store the outlet status at time of purchase.
 *
 * @internal
 */
const ORDER_ITEM_OUTLET_META_KEY = '_outletpro';

/**
 * Order item meta key used to store the outlet badge label at time of purchase.
 *
 * @internal
 */
const ORDER_ITEM_OUTLET_BADGE_LABEL_META_KEY = '_outletpro_badge_label';

/**
 * Helper to initialize admin order display hooks.
 *
 * @internal
 */
function init_admin_order(): void {
	add_action( 'woocommerce_after_order_itemmeta', 'OutletPro\display_order_item_outlet_badge_hook', 1, 3 );
	add_filter( 'woocommerce_hidden_order_itemmeta', 'OutletPro\hide_order_item_outlet_meta_hook' );
}

/**
 * Hide the outlet meta key from the default order item meta display.
 *
 * Fired by `woocommerce_hidden_order_itemmeta`.
 *
 * @param string[] $hidden_meta_keys The list of hidden meta keys.
 * @return string[] The updated list.
 * @internal WordPress filter hook
 */
function hide_order_item_outlet_meta_hook( array $hidden_meta_keys ): array {
	$hidden_meta_keys[] = ORDER_ITEM_OUTLET_META_KEY;
	$hidden_meta_keys[] = ORDER_ITEM_OUTLET_BADGE_LABEL_META_KEY;
	return $hidden_meta_keys;
}

/**
 * Display outlet badge text on the admin order screen.
 *
 * Fired by `woocommerce_before_order_itemmeta`.
 *
 * @param int            $_item_id The order item ID (unused).
 * @param \WC_Order_Item $item     The order item object.
 * @param mixed          $_product The product (unused).
 * @internal WordPress action hook
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
 */
function display_order_item_outlet_badge_hook( $_item_id, \WC_Order_Item $item, $_product ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	if ( 'yes' !== $item->get_meta( ORDER_ITEM_OUTLET_META_KEY ) ) {
		return;
	}

	$label = $item->get_meta( ORDER_ITEM_OUTLET_BADGE_LABEL_META_KEY );

	if ( ! is_string( $label ) || '' === $label ) {
		$label = __( '⚠️ Missing label', 'outletpro' );
	}

	echo '<span class="outletpro-admin-badge">' . esc_html( $label ) . '</span>';
}
