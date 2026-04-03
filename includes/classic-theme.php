<?php
/**
 * Classic theme integration functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize classic theme integrations.
 *
 * @since 1.0.0
 */
function classic_theme_init(): void {
	if ( wp_is_block_theme() ) {
		return;
	}

	add_action( 'woocommerce_single_product_summary', 'WC_Clearance\display_clearance_badge_hook', 15 );
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
		'<p><span class="wc-clearance-product-badge" style="background-color:#FFEE85; color:#222; display:inline-block; border-radius:2px; padding:0.35em 0.5em; line-height:1; font-weight:600; font-size:0.875em;">%s</span></p>',
		esc_html__( 'Clearance', 'wc-clearance' )
	);
}
