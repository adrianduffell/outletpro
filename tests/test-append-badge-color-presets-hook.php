<?php
/**
 * Tests for append_badge_color_presets_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_blocks;
use function OutletPro\init_blocks;
use function OutletPro\init_theme_json;
use const OutletPro\OUTLET_BADGE_BG_COLOR_OPTION;
use const OutletPro\OUTLET_BADGE_TEXT_COLOR_OPTION;

class Test_Append_Badge_Color_Presets_Hook extends WP_UnitTestCase {

	public function test_preserves_inherited_theme_colors(): void {
		// Arrange.
		add_filter( 'theme_root', static fn() => DIR_TESTDATA . '/themedir1' );
		switch_theme( 'block-theme-child-with-fluid-layout' );
		init_theme_json();
		deinit_blocks();
		init_blocks();
		update_option( OUTLET_BADGE_BG_COLOR_OPTION, '#123456' );
		update_option( OUTLET_BADGE_TEXT_COLOR_OPTION, '#ffffff' );
		wp_clean_theme_json_cache();

		// Act.
		$global  = wp_get_global_settings( array( 'color', 'palette', 'theme' ) );
		$tile    = wp_get_global_settings( array( 'color', 'palette', 'theme' ), array( 'block_name' => 'outletpro/outlet-tile' ) );
		$palette = array_column( $tile, null, 'slug' );

		// Assert.
		$this->assertSame( array( 'light', 'dark' ), array_column( $global, 'slug' ) );
		$this->assertSame( $global[0], $palette['light'] );
		$this->assertSame( $global[1], $palette['dark'] );
		$this->assertSame( '#123456', $palette['outletpro-badge-bg-color']['color'] );
		$this->assertSame( '#ffffff', $palette['outletpro-badge-text-color']['color'] );
	}

	public function test_adds_badge_colors_without_a_theme_palette(): void {
		// Arrange.
		init_theme_json();
		deinit_blocks();
		init_blocks();
		$theme_json = new WP_Theme_JSON_Data( array( 'version' => 3 ), 'blocks' );

		// Act.
		$filtered = apply_filters( 'wp_theme_json_data_blocks', $theme_json );
		$palette  = array_column( $filtered->get_data()['settings']['blocks']['outletpro/outlet-tile']['color']['palette']['theme'], null, 'slug' );

		// Assert.
		$this->assertSame( 'Outlet badge background color', $palette['outletpro-badge-bg-color']['name'] );
		$this->assertSame( 'Outlet badge text color', $palette['outletpro-badge-text-color']['name'] );
	}

	public function test_uses_fallbacks_for_empty_badge_colors(): void {
		// Arrange.
		init_theme_json();
		deinit_blocks();
		init_blocks();
		update_option( OUTLET_BADGE_BG_COLOR_OPTION, '' );
		update_option( OUTLET_BADGE_TEXT_COLOR_OPTION, '' );
		$theme_json = new WP_Theme_JSON_Data( array( 'version' => 3 ), 'blocks' );

		// Act.
		$filtered = apply_filters( 'wp_theme_json_data_blocks', $theme_json );
		$palette  = array_column( $filtered->get_data()['settings']['blocks']['outletpro/outlet-tile']['color']['palette']['theme'], null, 'slug' );

		// Assert.
		$this->assertSame( '#FFFFFF', $palette['outletpro-badge-bg-color']['color'] );
		$this->assertSame( '#000000', $palette['outletpro-badge-text-color']['color'] );
	}

	public function test_preserves_legacy_theme_colors(): void {
		// Arrange.
		switch_theme( 'storefront' );
		deinit_blocks();
		init_blocks();
		init_theme_json();
		$accent = array(
			'slug'  => 'legacy-accent',
			'name'  => 'Legacy accent',
			'color' => '#123456',
		);
		add_theme_support( 'editor-color-palette', array( $accent ) );
		wp_clean_theme_json_cache();

		// Act.
		$global = wp_get_global_settings( array( 'color', 'palette', 'theme' ) );
		$tile   = wp_get_global_settings( array( 'color', 'palette', 'theme' ), array( 'block_name' => 'outletpro/outlet-tile' ) );

		// Assert.
		$this->assertSame( array( $accent ), $global );
		$this->assertSame( $accent, array_column( $tile, null, 'slug' )['legacy-accent'] );
		$this->assertContains( 'outletpro-badge-bg-color', array_column( $tile, 'slug' ) );
		$this->assertContains( 'outletpro-badge-text-color', array_column( $tile, 'slug' ) );
	}
}
