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
	if ( wp_is_block_theme() ) {
		return;
	}

	add_action( 'woocommerce_single_product_summary', 'WC_Clearance\display_clearance_badge_hook', 19 );
}

/**
 * Display the clearance badge above the excerpt on single product pages (classic themes only).
 *
 * Fired by `woocommerce_single_product_summary`.
 *
 * @internal WordPress action hook
 */
function display_clearance_badge_hook(): void {
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

	echo '<span class="wc-clearance-product-badge" style="display:inline-block; border-radius:9999px; padding:0.25em 0.75em; background-color:#d63638; color:#fff; font-size:0.875em;">' . esc_html__( 'Choose carefully! Clearance products are ineligible for returns.', 'wc-clearance' ) . '</span>';
}
