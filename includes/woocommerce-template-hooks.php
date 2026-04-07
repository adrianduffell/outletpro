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
 * @internal
 */
function init_woocommerce_template_hooks(): void {
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

	$label = get_option( CLEARANCE_BADGE_LABEL_OPTION, __( 'Clearance', 'wc-clearance' ) );

	wp_enqueue_style( 'wc-clearance' );

	printf(
		'<p class="wc-clearance-badge-container"><span class="wc-clearance-badge">%s</span></p>',
		esc_html( $label )
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

	wp_enqueue_style( 'wc-clearance' );

	echo '<p class="wc-clearance-message">' . esc_html( get_option( CLEARANCE_MESSAGE_OPTION, __( 'Not eligible for change of mind returns', 'wc-clearance' ) ) ) . '</p>';
}
