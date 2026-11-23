<?php
/**
 * Tests for init_settings().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_settings;
use function OutletPro\deinit_blocks;
use function OutletPro\init_blocks;
use function OutletPro\init_settings;
use function OutletPro\init_theme_json;
use const OutletPro\OUTLET_BADGE_BG_COLOR_OPTION;

class Test_Init_Settings extends WP_UnitTestCase {

	public function test_refreshes_the_palette_when_a_badge_color_changes(): void {
		// Arrange.
		deinit_settings();
		deinit_blocks();
		init_blocks();
		init_theme_json();
		update_option( OUTLET_BADGE_BG_COLOR_OPTION, '#ff0000' );
		wp_clean_theme_json_cache();
		wp_get_global_settings();

		// Act.
		init_settings();
		update_option( OUTLET_BADGE_BG_COLOR_OPTION, '#008000' );
		$palette = wp_get_global_settings( array( 'color', 'palette', 'theme' ), array( 'block_name' => 'outletpro/outlet-tile' ) );
		$colors  = array_column( $palette, 'color', 'slug' );

		// Assert.
		$this->assertSame( '#008000', $colors['outletpro-badge-bg-color'] );
	}
}
