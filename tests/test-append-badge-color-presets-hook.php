<?php
/**
 * Tests for append_badge_color_presets_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\init_theme_json;
use const OutletPro\OUTLET_BADGE_BG_COLOR_OPTION;
use const OutletPro\OUTLET_BADGE_TEXT_COLOR_OPTION;

class Test_Append_Badge_Color_Presets_Hook extends WP_UnitTestCase {

	public function test_preserves_the_theme_palette(): void {
		// Arrange.
		init_theme_json();
		update_option( OUTLET_BADGE_BG_COLOR_OPTION, '#123456' );
		update_option( OUTLET_BADGE_TEXT_COLOR_OPTION, '#ffffff' );
		$accent     = array(
			'slug'  => 'accent',
			'name'  => 'Accent',
			'color' => '#123456',
		);
		$theme_json = new WP_Theme_JSON_Data(
			array(
				'version'  => 3,
				'settings' => array( 'color' => array( 'palette' => array( $accent ) ) ),
			),
			'theme'
		);

		// Act.
		$filtered = apply_filters( 'wp_theme_json_data_theme', $theme_json );
		$palette  = array_column( $filtered->get_data()['settings']['color']['palette']['theme'], null, 'slug' );

		// Assert.
		$this->assertSame( $accent, $palette['accent'] );
		$this->assertSame( '#123456', $palette['outletpro-badge-bg-color']['color'] );
		$this->assertSame( '#ffffff', $palette['outletpro-badge-text-color']['color'] );
	}

	public function test_adds_badge_colors_without_a_theme_palette(): void {
		// Arrange.
		init_theme_json();
		$theme_json = new WP_Theme_JSON_Data( array( 'version' => 3 ), 'theme' );

		// Act.
		$filtered = apply_filters( 'wp_theme_json_data_theme', $theme_json );
		$palette  = array_column( $filtered->get_data()['settings']['color']['palette']['theme'], null, 'slug' );

		// Assert.
		$this->assertSame( 'Outlet badge background color', $palette['outletpro-badge-bg-color']['name'] );
		$this->assertSame( 'Outlet badge text color', $palette['outletpro-badge-text-color']['name'] );
	}

	public function test_uses_fallbacks_for_empty_badge_colors(): void {
		// Arrange.
		init_theme_json();
		update_option( OUTLET_BADGE_BG_COLOR_OPTION, '' );
		update_option( OUTLET_BADGE_TEXT_COLOR_OPTION, '' );
		$theme_json = new WP_Theme_JSON_Data( array( 'version' => 3 ), 'theme' );

		// Act.
		$filtered = apply_filters( 'wp_theme_json_data_theme', $theme_json );
		$palette  = array_column( $filtered->get_data()['settings']['color']['palette']['theme'], null, 'slug' );

		// Assert.
		$this->assertSame( '#000000', $palette['outletpro-badge-bg-color']['color'] );
		$this->assertSame( '#FFFFFF', $palette['outletpro-badge-text-color']['color'] );
	}
}
