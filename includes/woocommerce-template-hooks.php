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
 * @throws \InvalidArgumentException When a filter returns an invalid value.
 */
function init_woocommerce_template_hooks(): void {
	$single_product_badge_hook = apply_filters(
		'wc_clearance_badge_single_product_hook',
		'woocommerce_single_product_summary'
	);

	if ( ! is_string( $single_product_badge_hook ) || '' === trim( $single_product_badge_hook ) ) {
		throw new \InvalidArgumentException( 'The wc_clearance_badge_single_product_hook filter must return a non-empty string.' );
	}

	$single_product_badge_priority = apply_filters(
		'wc_clearance_badge_single_product_priority',
		15
	);

	if ( ! is_int( $single_product_badge_priority ) ) {
		throw new \InvalidArgumentException( 'The wc_clearance_badge_single_product_priority filter must return an integer.' );
	}

	add_action( $single_product_badge_hook, 'WC_Clearance\display_clearance_badge_hook', $single_product_badge_priority );
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

	$bg_colour   = sanitize_hex_color( get_theme_mod( CLEARANCE_BADGE_BG_COLOUR_MOD, CLEARANCE_BADGE_BG_COLOUR_DEFAULT ) );
	$text_colour = sanitize_hex_color( get_theme_mod( CLEARANCE_BADGE_TEXT_COLOUR_MOD, CLEARANCE_BADGE_TEXT_COLOUR_DEFAULT ) );
	$label       = get_option( CLEARANCE_BADGE_LABEL_OPTION, __( 'Clearance', 'wc-clearance' ) );

	wp_enqueue_style( 'wc-clearance' );

	printf(
		'<p class="wc-clearance-badge-container"><span class="wc-clearance-badge" style="background-color:%s; color:%s;">%s</span></p>',
		esc_attr( $bg_colour ),
		esc_attr( $text_colour ),
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
