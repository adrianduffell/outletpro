<?php
/**
 * Tests for outlet menu font size selection in patterns.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_patterns;
use function OutletPro\get_outlet_menu_font_size_preset;
use function OutletPro\get_pattern_content;
use function OutletPro\init_patterns;
use function OutletPro\init_taxonomies;

class Test_Get_Outlet_Menu_Font_Size_Preset extends WP_UnitTestCase {

	public function test_patterns_use_normal_when_the_theme_defines_it(): void {
		// Arrange.
		switch_theme( 'storefront' );
		add_theme_support(
			'editor-font-sizes',
			array(
				array(
					'name' => 'Normal',
					'slug' => 'normal',
					'size' => 16,
				),
				array(
					'name' => 'Medium',
					'slug' => 'medium',
					'size' => 23,
				),
			)
		);
		wp_clean_theme_json_cache();
		init_taxonomies();
		deinit_patterns();
		init_patterns();

		// Act.
		$preset       = get_outlet_menu_font_size_preset();
		$menu_presets = array_map(
			static fn( string $pattern ): string => parse_blocks( get_pattern_content( $pattern ) )[0]['innerBlocks'][1]['attrs']['fontSize'],
			array(
				'outletpro/outlet-navigation-3',
				'outletpro/outlet-navigation-4',
				'outletpro/outlet-navigation-6',
				'outletpro/outlet-navigation-price-tiers',
			)
		);

		// Assert.
		$this->assertSame( 'normal', $preset );
		$this->assertSame( array_fill( 0, 4, 'normal' ), $menu_presets );
	}

	public function test_patterns_retain_medium_when_the_theme_has_no_normal_preset(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		remove_theme_support( 'editor-font-sizes' );
		wp_clean_theme_json_cache();
		init_taxonomies();
		deinit_patterns();
		init_patterns();

		// Act.
		$preset       = get_outlet_menu_font_size_preset();
		$menu_presets = array_map(
			static fn( string $pattern ): string => parse_blocks( get_pattern_content( $pattern ) )[0]['innerBlocks'][1]['attrs']['fontSize'],
			array(
				'outletpro/outlet-navigation-3',
				'outletpro/outlet-navigation-4',
				'outletpro/outlet-navigation-6',
				'outletpro/outlet-navigation-price-tiers',
			)
		);

		// Assert.
		$this->assertSame( 'medium', $preset );
		$this->assertSame( array_fill( 0, 4, 'medium' ), $menu_presets );
	}
}
