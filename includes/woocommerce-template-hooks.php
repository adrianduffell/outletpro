<?php
/**
 * WooCommerce template hook functions.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize classic theme frontend integrations.
 *
 * @internal
 * @throws \InvalidArgumentException When a setting or filter returns an invalid value.
 */
function init_woocommerce_template_hooks(): void {
	init_single_product_badge_template_hook();
	init_single_product_message_template_hook();
}

/**
 * Register the classic badge at the active theme's configured hook and priority.
 *
 * @internal
 * @throws \InvalidArgumentException When the setting or a filter returns an invalid value.
 */
function init_single_product_badge_template_hook(): void {
	$tuple = get_theme_mod( SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD, array( 'woocommerce_single_product_summary', 1 ) );
	if ( array() === $tuple ) {
		return;
	}

	/**
	 * Filters the hook used to display the outlet badge.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name The template hook name to display the outlet badge.
	 */
	$single_product_badge_hook = apply_filters(
		'outletpro_badge_single_product_hook',
		get_tuple_template_hook_name( $tuple )
	);

	if ( ! is_string( $single_product_badge_hook ) ) {
		throw new \InvalidArgumentException( 'The outletpro_badge_single_product_hook filter must return a non-empty string.' );
	}

	if ( '' === $single_product_badge_hook ) {
		throw new \InvalidArgumentException( 'The outletpro_badge_single_product_hook filter must return a non-empty string.' );
	}

	/**
	 * Filters the priority used in the template hook to display the outlet badge.
	 *
	 * @since 1.0.0
	 *
	 * @param int $priority The priority to display the outlet badge.
	 */
	$single_product_badge_priority = apply_filters(
		'outletpro_badge_single_product_priority',
		get_tuple_template_hook_priority( $tuple )
	);

	if ( ! is_int( $single_product_badge_priority ) ) {
		throw new \InvalidArgumentException( 'The outletpro_badge_single_product_priority filter must return an integer.' );
	}

	add_action( $single_product_badge_hook, 'OutletPro\display_outlet_badge_hook', $single_product_badge_priority );
}

/**
 * Register the classic message at the active theme's configured hook and priority.
 *
 * @internal
 * @throws \InvalidArgumentException When the setting is invalid.
 */
function init_single_product_message_template_hook(): void {
	$tuple = get_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD, array( 'woocommerce_single_product_summary', 11 ) );
	if ( array() === $tuple ) {
		return;
	}

	add_action( get_tuple_template_hook_name( $tuple ), 'OutletPro\display_outlet_message_hook', get_tuple_template_hook_priority( $tuple ) );
}

/**
 * Get the hook name from a template hook tuple.
 *
 * @internal
 * @param mixed $tuple Hook name and priority tuple.
 * @return string Hook name.
 * @throws \InvalidArgumentException When the tuple or hook name is invalid.
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
 */
function get_tuple_template_hook_name( $tuple ): string {
	if ( ! is_array( $tuple ) ) {
		throw new \InvalidArgumentException( 'The template hook setting must be an array.' );
	}

	if ( array( 0, 1 ) !== array_keys( $tuple ) ) {
		throw new \InvalidArgumentException( 'The template hook setting must contain a hook name and priority.' );
	}

	if ( ! is_string( $tuple[0] ) ) {
		throw new \InvalidArgumentException( 'The template hook name must be a non-empty string.' );
	}

	if ( '' === $tuple[0] ) {
		throw new \InvalidArgumentException( 'The template hook name must be a non-empty string.' );
	}

	return $tuple[0];
}

/**
 * Get the priority from a template hook tuple.
 *
 * @internal
 * @param mixed $tuple Hook name and priority tuple.
 * @return int Hook priority.
 * @throws \InvalidArgumentException When the tuple or priority is invalid.
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
 */
function get_tuple_template_hook_priority( $tuple ): int {
	if ( ! is_array( $tuple ) ) {
		throw new \InvalidArgumentException( 'The template hook setting must be an array.' );
	}

	if ( array( 0, 1 ) !== array_keys( $tuple ) ) {
		throw new \InvalidArgumentException( 'The template hook setting must contain a hook name and priority.' );
	}

	if ( ! is_int( $tuple[1] ) ) {
		throw new \InvalidArgumentException( 'The template hook priority must be an integer.' );
	}

	return $tuple[1];
}

/**
 * Output the outlet badge above the product name on single product pages.
 *
 * Fired by the configured WooCommerce single product hook.
 *
 * @internal WordPress action hook
 */
function display_outlet_badge_hook(): void {
	$product = wc_get_product( get_the_ID() );

	if ( ! $product instanceof \WC_Product ) {
		return;
	}

	if ( ! taxonomy_exists( OUTLET_STATUS_TAXONOMY ) || ! is_outlet( $product ) ) {
		return;
	}

	$label = get_option( OUTLET_BADGE_LABEL_OPTION );

	if ( ! is_string( $label ) || '' === $label ) {
		return;
	}

	wp_enqueue_style( 'outletpro-classic-badge' );

	printf(
		'<div class="outletpro-badge">%s</div>',
		esc_html( $label )
	);
}

/**
 * Display the outlet message on single product pages (classic themes only).
 *
 * Fired by the configured WooCommerce single product hook.
 *
 * @internal WordPress action hook
 */
function display_outlet_message_hook(): void {
	$product = wc_get_product( get_the_ID() );

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

	$message = get_option( OUTLET_MESSAGE_OPTION );

	if ( ! is_string( $message ) || '' === $message ) {
		return;
	}

	wp_enqueue_style( 'outletpro-classic-message' );

	echo '<p class="outletpro-message">' . esc_html( $message ) . '</p>';
}
