<?php
/**
 * Block editor integration functions.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize block editor integrations.
 *
 * @internal
 */
function init_block_editor(): void {
	add_filter( 'block_editor_settings_all', 'OutletPro\append_block_editor_settings_hook', 10, 2 );
	add_filter( 'block_editor_settings_all', 'OutletPro\append_price_tier_editor_settings_hook' );
	add_filter( 'block_editor_settings_all', 'OutletPro\append_discount_tier_editor_settings_hook' );
}

/**
 * Append data to the block editor settings.
 *
 * - outletproIsBlockTheme: boolean indicating if the current theme is a block theme.
 * - outletproMenuItems: Available outlet menu selections.
 * - outletproMenuPermalink: Current outlet page permalink for the editor.
 * - wcOutletCanonicalTermId: ID of the canonical term for the outlet status taxonomy.
 *
 * Fired by `block_editor_settings_all`.
 *
 * @internal WordPress filter hook
 *
 * @param array<string, mixed>     $settings Existing block editor settings.
 * @param \WP_Block_Editor_Context $_context The block editor context.
 * @return array<string, mixed> Modified block editor settings.
 */
function append_block_editor_settings_hook( array $settings, \WP_Block_Editor_Context $_context ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	$settings['outletproIsBlockTheme'] = wp_is_block_theme();

	$settings['outletproMenuItems'] = array(
		'categories' => array(),
		'prices'     => array(),
		'discounts'  => array(),
	);

	$permalink = get_outlet_permalink();

	$settings['outletproMenuPermalink'] = $permalink;
	if ( is_string( $permalink ) ) {
		$menu_categories = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'parent'     => 0,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);

		if ( ! is_wp_error( $menu_categories ) ) {
			foreach ( $menu_categories as $category ) {
				$settings['outletproMenuItems']['categories'][] = array(
					'id'    => $category->term_id,
					'name'  => html_entity_decode( $category->name, ENT_QUOTES, 'UTF-8' ),
					'query' => 'outlet_category=' . rawurlencode( $category->slug ),
				);
			}
		}
		foreach ( get_price_tiers() as $price ) {
			$term = get_term_by( 'slug', 'outlet-price-' . $price, OUTLET_SEARCH_INDEX_TAXONOMY );
			if ( ! $term instanceof \WP_Term ) {
				continue;
			}
			// translators: %s: Formatted price tier amount.
			$name = sprintf( __( 'Under %s', 'outletpro' ), wc_price( $price, array( 'decimals' => 0 ) ) );

			$settings['outletproMenuItems']['prices'][] = array(
				'id'    => $term->term_id,
				'name'  => html_entity_decode( wp_strip_all_tags( $name ), ENT_QUOTES, 'UTF-8' ),
				'query' => 'outlet_price=' . $price,
			);
		}
		foreach ( get_discount_tiers() as $discount ) {
			$term = get_term_by( 'slug', 'outlet-discount-' . $discount, OUTLET_SEARCH_INDEX_TAXONOMY );
			if ( ! $term instanceof \WP_Term ) {
				continue;
			}
			// translators: %d: Discount tier percentage.
			$name = sprintf( __( '%d%% off', 'outletpro' ), $discount );

			$settings['outletproMenuItems']['discounts'][] = array(
				'id'    => $term->term_id,
				'name'  => $name,
				'query' => 'outlet_discount=' . $discount,
			);
		}
	}

	$canonical_term = get_term_by( 'name', OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY );

	if ( ! $canonical_term instanceof \WP_Term ) {
		return $settings;
	}

	$settings['wcOutletCanonicalTermId'] = $canonical_term->term_id;

	return $settings;
}

/**
 * Append price tiers to the block editor settings.
 *
 * Fired by `block_editor_settings_all`.
 *
 * @internal WordPress filter hook
 *
 * @param array<string, mixed> $settings Existing block editor settings.
 * @return array<string, mixed> Modified block editor settings.
 */
function append_price_tier_editor_settings_hook( array $settings ): array {
	$settings['outletproPriceTiers'] = array();

	try {
		$price_tiers = get_price_tiers();
	} catch ( \RuntimeException $e ) {
		wc_get_logger()->error( 'Outlet price tiers could not be retrieved for the editor.' );
		return $settings;
	}

	foreach ( $price_tiers as $price ) {
		$term = get_term_by( 'slug', 'outlet-price-' . $price, OUTLET_SEARCH_INDEX_TAXONOMY );
		if ( ! $term instanceof \WP_Term ) {
			continue;
		}
		// translators: %s: Formatted price tier amount.
		$name = sprintf( __( 'Under %s', 'outletpro' ), wc_price( $price, array( 'decimals' => 0 ) ) );

		$tier       = new \stdClass();
		$tier->id   = $term->term_id;
		$tier->name = html_entity_decode( wp_strip_all_tags( $name ), ENT_QUOTES, 'UTF-8' );

		$settings['outletproPriceTiers'][] = $tier;
	}

	return $settings;
}

/**
 * Append discount tiers to the block editor settings.
 *
 * Fired by `block_editor_settings_all`.
 *
 * @internal WordPress filter hook
 *
 * @param array<string, mixed> $settings Existing block editor settings.
 * @return array<string, mixed> Modified block editor settings.
 */
function append_discount_tier_editor_settings_hook( array $settings ): array {
	$settings['outletproDiscountTiers'] = array();

	try {
		$discount_tiers = get_discount_tiers();
	} catch ( \RuntimeException $e ) {
		wc_get_logger()->error( 'Outlet discount tiers could not be retrieved for the editor.' );
		return $settings;
	}

	foreach ( $discount_tiers as $discount ) {
		$term = get_term_by( 'slug', 'outlet-discount-' . $discount, OUTLET_SEARCH_INDEX_TAXONOMY );
		if ( ! $term instanceof \WP_Term ) {
			continue;
		}
		// translators: %d: Discount tier percentage.
		$name = sprintf( __( '%d%% off', 'outletpro' ), $discount );

		$tier       = new \stdClass();
		$tier->id   = $term->term_id;
		$tier->name = $name;

		$settings['outletproDiscountTiers'][] = $tier;
	}

	return $settings;
}
