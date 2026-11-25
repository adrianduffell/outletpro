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
 * WordPress theme mod key used to store the block hook for the product catalog conditional block.
 *
 * @internal
 */
const PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD = 'outletpro_product_catalog_conditional_block_hook';

/**
 * WordPress theme mod key used to store the block hook for the single product message block.
 *
 * @internal
 */
const SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD = 'outletpro_single_product_message_block_hook';

/**
 * WordPress theme mod key used to store the style preset when rendered on the single product.
 *
 * @internal
 */
const SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD = 'outletpro_single_product_message_block_style';

/**
 * WordPress theme mod key used to control strong text on outlet messages.
 *
 * @internal
 */
const SINGLE_PRODUCT_MESSAGE_BOLD_THEME_MOD = 'outletpro_single_product_message_bold';

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
 * WordPress theme mod key used to control the outlet page link on outlet messages.
 *
 * @internal
 */
const SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD = 'outletpro_single_product_message_show_link';

/**
 * WordPress theme mod key used to store the template hook for the single product badge.
 *
 * @internal
 */
const SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD = 'outletpro_single_product_badge_template_hook';

/**
 * WordPress theme mod key used to store the template hook for the outlet hero widgets.
 *
 * @internal
 */
const OUTLET_HERO_TEMPLATE_HOOK_THEME_MOD = 'outletpro_outlet_hero_template_hook';

/**
 * WordPress theme mod key used to store the outlet hero's selected pattern.
 *
 * @internal
 */
const OUTLET_HERO_PATTERN_THEME_MOD = 'outletpro_outlet_hero_pattern';

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
	try {
		seed_theme_mods();
	} catch ( \RuntimeException $e ) {
		wc_get_logger()->error( 'Theme mods could not be seeded.' );
	}
}

/**
 * Seed per-theme settings as theme mods.
 *
 * @internal
 * @throws \RuntimeException If product categories cannot be retrieved.
 */
function seed_theme_mods(): void {
	$mods = get_theme_mods();

	switch ( wp_is_block_theme() ) {
		case true:
			if ( ! array_key_exists( OUTLET_HERO_PATTERN_THEME_MOD, $mods ) ) {
				set_theme_mod( OUTLET_HERO_PATTERN_THEME_MOD, select_outlet_navigation_pattern() );
			}
			if ( ! array_key_exists( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD, $mods ) ) {
				$block_hook = select_outlet_hero_anchor();
				if ( null !== $block_hook ) {
					set_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD, $block_hook );
				}
			}
			if ( ! array_key_exists( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD, $mods ) ) {
				set_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD, array( 'core/post-title', 'before' ) );
			}
			if ( ! array_key_exists( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, $mods ) ) {
				if ( 'purple' === get_template() ) {
					set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-details', 'after' ) );
				} else {
					set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-price', 'after' ) );
				}
			}
			if ( ! array_key_exists( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD, $mods ) ) {
				set_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD, true );
			}
			if ( ! array_key_exists( SINGLE_PRODUCT_MESSAGE_BOLD_THEME_MOD, $mods ) ) {
				set_theme_mod( SINGLE_PRODUCT_MESSAGE_BOLD_THEME_MOD, true );
			}
			if ( ! array_key_exists( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD, $mods ) ) {
				set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD, 'fine' );
			}
			break;
		case false:
			if ( ! array_key_exists( OUTLET_HERO_TEMPLATE_HOOK_THEME_MOD, $mods ) ) {
				set_theme_mod( OUTLET_HERO_TEMPLATE_HOOK_THEME_MOD, array( 'woocommerce_archive_description', 5 ) );
			}
			if ( ! array_key_exists( SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD, $mods ) ) {
				set_theme_mod( SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD, array( 'woocommerce_single_product_summary', 1 ) );
			}
			if ( ! array_key_exists( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD, $mods ) ) {
				set_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD, array( 'woocommerce_single_product_summary', 11 ) );
			}
			if ( ! array_key_exists( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD, $mods ) ) {
				set_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD, true );
			}
			break;
	}
}

/**
 * Select an outlet navigation using store heuristics.
 *
 * The heuristics are:
 * - Default: Outlet navigation with three category tiles.
 * - 0-2 top-level product categories: Outlet navigation with circular price tier tiles.
 * - 4 top-level product categories: Outlet navigation with four category tiles.
 * - 6 top-level product categories: Outlet navigation with six category tiles.
 *
 * Product category counts exclude the uncategorized category.
 *
 * @internal
 * @throws \RuntimeException When product categories cannot be retrieved.
 */
function select_outlet_navigation_pattern(): string {
	$excluded_categories = array_filter( array( get_uncategorized_product_category_id() ) );

	$categories = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => 0,
			'exclude'    => $excluded_categories,
			'hide_empty' => false,
			'fields'     => 'ids',
			'number'     => 7,
		)
	);
	if ( is_wp_error( $categories ) ) {
		throw new \RuntimeException( 'Product categories could not be retrieved.' );
	}

	switch ( count( $categories ) ) {
		case 0:
		case 1:
		case 2:
			return 'outletpro/outlet-navigation-price-tiers';
		case 4:
			return 'outletpro/outlet-navigation-4';
		case 6:
			return 'outletpro/outlet-navigation-6';
		default:
			return 'outletpro/outlet-navigation-3';
	}
}

/**
 * Selects the block hook anchor for auto-inserting the outlet hero.
 *
 * Selects store notices when an archive title exists and the notices are below
 * the term description, or below the title if no description exists.
 *
 * Otherwise, selects term description.
 *
 * The hero is inserted after the selected anchor.
 *
 * Returns null when the catalog template is missing.
 *
 * @internal
 * @return string[]|null Anchor block name and relative position, or null when the template is missing.
 */
function select_outlet_hero_anchor(): ?array {
	$catalog_template = get_block_template( get_stylesheet() . '//archive-product' );
	if ( null === $catalog_template ) {
		return null;
	}

	$default              = array( 'core/term-description', 'after' );
	$blocks               = resolve_pattern_blocks( parse_blocks( $catalog_template->content ) );
	$anchors              = get_catalog_anchor_order( $blocks );
	$title_position       = array_search( 'core/query-title', $anchors, true );
	$description_position = array_search( 'core/term-description', $anchors, true );
	$notices_position     = array_search( 'woocommerce/store-notices', $anchors, true );
	if ( false === $title_position ) {
		return $default;
	}
	if ( false === $notices_position ) {
		return $default;
	}
	$reference_position = $description_position;
	if ( false === $reference_position ) {
		$reference_position = $title_position;
	}
	if ( $notices_position > $reference_position ) {
		return array( 'woocommerce/store-notices', 'after' );
	}
	return $default;
}

/**
 * Read catalog landmark blocks in document order, including nested containers.
 *
 * @internal
 * @param array<array<string, mixed>> $blocks Parsed catalog blocks.
 * @return string[] Landmark block names.
 */
function get_catalog_anchor_order( array $blocks ): array {
	$anchors = array();
	foreach ( $blocks as $block ) {
		if ( in_array( $block['blockName'], array( 'core/query-title', 'core/term-description', 'woocommerce/store-notices' ), true ) ) {
			$anchors[] = $block['blockName'];
		}
		$anchors = array_merge( $anchors, get_catalog_anchor_order( $block['innerBlocks'] ) );
	}
	return $anchors;
}
