<?php
/**
 * WooCommerce template hook functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize classic theme frontend integrations.
 *
 * @since 1.0.0
 */
function init_classic_themes(): void {
	add_action( 'woocommerce_single_product_summary', 'WC_Clearance\display_clearance_message_hook', 19 );
}

/**
 * Display the clearance message above the excerpt on single product pages (classic themes only).
 *
 * Fired by `woocommerce_single_product_summary`.
 *
 * @internal WordPress action hook
 */
function display_clearance_message_hook(): void {
	if ( wp_is_block_theme() ) {
		return;
	}

	$product = wc_get_product( get_the_ID() );

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

	echo '<p class="wc-clearance-product-message" style="font-weight:bold;">' . esc_html__( 'Choose carefully! Clearance products are ineligible for returns.', 'wc-clearance' ) . '</p>';
}
