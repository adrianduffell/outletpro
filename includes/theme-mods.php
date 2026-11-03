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
				set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-meta', 'first_child' ) );
			}
			break;
		case false:
			if ( ! array_key_exists( SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD, $mods ) ) {
				set_theme_mod( SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD, array( 'woocommerce_single_product_summary', 1 ) );
			}
			if ( ! array_key_exists( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD, $mods ) ) {
				set_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD, array( 'woocommerce_product_meta_start', 1 ) );
			}
			break;
	}
}
