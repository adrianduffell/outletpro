<?php
/**
 * Block pattern registration.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize block patterns.
 *
 * @internal
 */
function init_patterns(): void {
	register_outlet_block_pattern_category();
	register_outlet_badge_row_pattern();
	register_outlet_navigation_3_pattern();
	register_outlet_navigation_4_pattern();
	register_outlet_navigation_price_tiers_pattern();
	register_outlet_navigation_6_pattern();

	if ( version_compare( get_bloginfo( 'version' ), '7.0', '<' ) ) {
		return;
	}

	register_outlet_sort_filter_pattern();
}

/**
 * Helper to de-initialize block patterns back to the uninitialized state.
 *
 * @internal
 */
function deinit_patterns(): void {
	$patterns = array_filter(
		\WP_Block_Patterns_Registry::get_instance()->get_all_registered(),
		fn( $pattern ) => 0 === strpos( $pattern['name'], 'outletpro/' )
	);

	foreach ( $patterns as $pattern ) {
		unregister_block_pattern( $pattern['name'] );
	}

	$categories = array_filter(
		\WP_Block_Pattern_Categories_Registry::get_instance()->get_all_registered(),
		fn( $category ) => 0 === strpos( $category['name'], 'outletpro' )
	);

	foreach ( $categories as $category ) {
		unregister_block_pattern_category( $category['name'] );
	}
}

/**
 * Get block content for a registered pattern, including editor metadata.
 *
 * @param string $pattern_name Pattern slug.
 *
 * @throws \InvalidArgumentException If the pattern name is empty or has unsupported characters.
 * @throws \InvalidArgumentException If the pattern is not registered.
 * @throws \RuntimeException If block parsing fails.
 * @throws \RuntimeException If pattern resolution fails.
 * @throws \RuntimeException If block serialization fails.
 */
function get_pattern_content( string $pattern_name ): string {
	if ( '' === $pattern_name ) {
		throw new \InvalidArgumentException( 'Pattern name cannot be empty.' );
	}

	if ( ! preg_match( '/^[a-z0-9\\/-]+$/', $pattern_name ) ) {
		throw new \InvalidArgumentException( 'Pattern name contains unsupported characters.' );
	}

	if ( ! \WP_Block_Patterns_Registry::get_instance()->is_registered( $pattern_name ) ) {
		throw new \InvalidArgumentException( 'Block pattern is not registered.' );
	}

	$parsed_blocks = parse_blocks(
		sprintf(
			'<!-- wp:pattern {"slug":"%s"} /-->',
			$pattern_name
		)
	);

	if ( empty( $parsed_blocks ) ) {
		throw new \RuntimeException( 'Failed to parse block pattern markup.' );
	}

	$resolved_blocks = resolve_pattern_blocks( $parsed_blocks );

	if ( empty( $resolved_blocks ) ) {
		throw new \RuntimeException( 'Failed to resolve block pattern markup.' );
	}

	$content = serialize_blocks( $resolved_blocks );

	if ( '' === $content ) {
		throw new \RuntimeException( 'Failed to serialize resolved block pattern markup.' );
	}

	return $content;
}

/**
 * Get the preferred font size preset for outlet menus in patterns.
 *
 * @internal
 * @return string Font size preset slug.
 */
function get_outlet_menu_font_size_preset(): string {
	$font_sizes_by_origin = wp_get_global_settings( array( 'typography', 'fontSizes' ) );
	$font_sizes           = array_merge( ...array_values( $font_sizes_by_origin ) );

	return in_array( 'normal', array_column( $font_sizes, 'slug' ), true ) ? 'normal' : 'medium';
}

/**
 * Register the outlet navigation with three category tiles pattern.
 *
 * @internal
 */
function register_outlet_navigation_3_pattern(): void {
	register_block_pattern(
		'outletpro/outlet-navigation-3',
		array(
			'title'         => __( 'Outlet navigation with three category tiles', 'outletpro' ),
			'description'   => __( 'Displays three tiles and a menu.', 'outletpro' ),
			'filePath'      => dirname( PLUGIN_FILE ) . '/templates/outlet-three-tiles-pattern.php',
			'categories'    => array( 'outletpro' ),
			'viewportWidth' => 1000,
		)
	);
}

/**
 * Register the outlet navigation with four category tiles pattern.
 *
 * @internal
 */
function register_outlet_navigation_4_pattern(): void {
	register_block_pattern(
		'outletpro/outlet-navigation-4',
		array(
			'title'         => __( 'Outlet navigation with four category tiles', 'outletpro' ),
			'description'   => __( 'Displays four tiles and a menu.', 'outletpro' ),
			'filePath'      => dirname( PLUGIN_FILE ) . '/templates/outlet-four-tiles-pattern.php',
			'categories'    => array( 'outletpro' ),
			'viewportWidth' => 1000,
		)
	);
}

/**
 * Register the price tiers with circles pattern.
 *
 * @internal
 */
function register_outlet_navigation_price_tiers_pattern(): void {
	register_block_pattern(
		'outletpro/outlet-navigation-price-tiers',
		array(
			'title'         => __( 'Outlet navigation with circular price tier tiles', 'outletpro' ),
			'description'   => __( 'Displays four price tier tiles and a menu.', 'outletpro' ),
			'filePath'      => dirname( PLUGIN_FILE ) . '/templates/outlet-price-circles-pattern.php',
			'categories'    => array( 'outletpro' ),
			'viewportWidth' => 1000,
		)
	);
}

/**
 * Register the outlet navigation with six category tiles pattern.
 *
 * @internal
 */
function register_outlet_navigation_6_pattern(): void {
	register_block_pattern(
		'outletpro/outlet-navigation-6',
		array(
			'title'         => __( 'Outlet navigation with six category tiles', 'outletpro' ),
			'description'   => __( 'Displays six tiles and a menu.', 'outletpro' ),
			'filePath'      => dirname( PLUGIN_FILE ) . '/templates/outlet-six-tiles-pattern.php',
			'categories'    => array( 'outletpro' ),
			'viewportWidth' => 1000,
		)
	);
}

/**
 * Get the block markup content for the outlet sort filter pattern.
 *
 * @internal
 * @return string Block markup string.
 */
function get_outlet_sort_filter_pattern_content(): string {
	$template_path = dirname( PLUGIN_FILE ) . '/templates/outlet-sort-filter-pattern.php';

	if ( ! is_readable( $template_path ) ) {
		return '';
	}

	ob_start();
	include $template_path;
	$template_content = ob_get_clean();

	if ( false === $template_content ) {
		return '';
	}

	return $template_content;
}

/**
 * Register the outlet sort filter block pattern.
 *
 * @internal
 */
function register_outlet_sort_filter_pattern(): void {
	register_block_pattern(
		'outletpro/outlet-sort-filter',
		array(
			'title'         => __( 'Outlet sort filter', 'outletpro' ),
			'description'   => __( 'Dropdown sort filter for the outlet page.', 'outletpro' ),
			'content'       => get_outlet_sort_filter_pattern_content(),
			'categories'    => array( 'outletpro' ),
			'viewportWidth' => 180,
		)
	);
}

/**
 * Registers the "Outlet Badge row" block pattern.
 *
 * @internal
 */
function register_outlet_badge_row_pattern(): void {
	register_block_pattern(
		'outletpro/outlet-badge-row',
		array(
			'title'         => __( 'Outlet Badge row', 'outletpro' ),
			'description'   => __( 'Displays the outlet badge in a row, aligned left.', 'outletpro' ),
			'filePath'      => dirname( PLUGIN_FILE ) . '/templates/outlet-badge-row-pattern.html',
			'categories'    => array( 'outletpro' ),
			'viewportWidth' => 320,
		)
	);
}

/**
 * Register the outlet block pattern category.
 *
 * @internal
 */
function register_outlet_block_pattern_category(): void {
	register_block_pattern_category(
		'outletpro',
		array(
			'label' => __( 'Outlet', 'outletpro' ),
		)
	);
}
