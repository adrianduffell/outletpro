<?php
/**
 * Theme mod functions.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * WordPress theme mod key used to store the block hook for the single product message block.
 *
 * @internal
 */
const SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD = 'outletpro_single_product_message_block_hook';

/**
 * WordPress theme mod key used to store the block hook for the single product badge block.
 *
 * @internal
 */
const SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD = 'outletpro_single_product_badge_block_hook';

/**
 * WordPress theme mod key used to store the template hook for the single product message.
 *
 * @internal
 */
const SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD = 'outletpro_single_product_message_template_hook';

/**
 * WordPress theme mod key used to store the template hook for the single product badge.
 *
 * @internal
 */
const SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD = 'outletpro_single_product_badge_template_hook';

/**
 * WordPress theme mod key used to show the outlet page link in the classic single product message.
 *
 * @internal
 */
const SINGLE_PRODUCT_MESSAGE_SHOW_OUTLET_LINK_THEME_MOD = 'outletpro_single_product_message_show_outlet_link';

/**
 * Initialize theme mod integrations.
 *
 * @internal
 */
function init_theme_mods(): void {
	add_action( 'after_switch_theme', 'OutletPro\after_switch_theme_hook' );
}

/**
 * Remove theme mod integrations.
 *
 * @internal
 */
function deinit_theme_mods(): void {
	remove_action( 'after_switch_theme', 'OutletPro\after_switch_theme_hook' );
}

/**
 * Seed missing theme mods after switching themes.
 *
 * Fired by `after_switch_theme`.
 *
 * @internal WordPress action hook
 */
function after_switch_theme_hook(): void {
	seed_theme_mods();
}

/**
 * Check whether the single product template contains a block, including referenced patterns.
 *
 * @internal
 * @param string $block_type Block type to find.
 */
function single_product_template_has_block( string $block_type ): bool {
	$template = get_block_template( get_stylesheet() . '//single-product', 'wp_template' );

	if ( null === $template ) {
		return false;
	}

	$blocks = resolve_pattern_blocks( parse_blocks( $template->content ) );

	return has_block( $block_type, serialize_blocks( $blocks ) );
}

/**
 * Seed missing placement mods appropriate to the active theme's type.
 *
 * @internal
 */
function seed_theme_mods(): void {
	$mods = get_theme_mods();

	switch ( wp_is_block_theme() ) {
		case true:
			if ( ! array_key_exists( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD, $mods ) ) {
				set_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD, array( 'core/post-title', 'before' ) );
			}
			if ( ! array_key_exists( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, $mods ) ) {
				if ( 'purple' === get_template() ) {
					set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-details', 'after' ) );
				} else {
					$message_hook = single_product_template_has_block( 'woocommerce/product-meta' )
						? array( 'woocommerce/product-meta', 'first_child' )
						: array( 'woocommerce/product-details', 'after' );
					set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, $message_hook );
				}
			}
			break;
		case false:
			if ( ! array_key_exists( SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD, $mods ) ) {
				set_theme_mod( SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD, array( 'woocommerce_single_product_summary', 1 ) );
			}
			if ( ! array_key_exists( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD, $mods ) ) {
				set_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD, array( 'woocommerce_single_product_summary', 1 ) );
			}
			if ( ! array_key_exists( SINGLE_PRODUCT_MESSAGE_SHOW_OUTLET_LINK_THEME_MOD, $mods ) ) {
				set_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_OUTLET_LINK_THEME_MOD, false );
			}
			break;
	}
}
