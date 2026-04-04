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
function init_woocommerce_template_hooks(): void {
	if ( wp_is_block_theme() ) {
		return;
	}
	add_action( 'woocommerce_single_product_summary', 'WC_Clearance\display_clearance_badge_hook', 15 );
	add_action( 'woocommerce_product_meta_start', 'WC_Clearance\display_clearance_message_hook', 1 );
}

/**
 * Output the clearance badge above the product excerpt on single product pages.
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

	if ( ! taxonomy_exists( CLEARANCE_STATUS_TAXONOMY ) || ! is_clearance( $product ) ) {
		return;
	}

	printf(
		'<p class="wc-clearance-badge-container"><span class="wc-clearance-badge" style="background-color:#FFEE85; color:#222; display:inline-block; border-radius:2px; padding:0.35em 0.5em; line-height:1; font-weight:600; font-size:0.875em;">%s</span></p>',
		esc_html__( 'Clearance', 'wc-clearance' )
	);
}

/**
 * Display the clearance message in the product meta area on single product pages (classic themes only).
 *
 * Fired by `woocommerce_product_meta_start`.
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
