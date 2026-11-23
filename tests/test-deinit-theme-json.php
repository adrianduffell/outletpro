<?php
/**
 * Tests for deinit_theme_json().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_theme_json;
use function OutletPro\init_theme_json;

class Test_Deinit_Theme_Json extends WP_UnitTestCase {

	public function test_disables_badge_presets(): void {
		// Arrange.
		init_theme_json();
		$theme_json = new WP_Theme_JSON_Data( array( 'version' => 3 ), 'theme' );

		// Act.
		deinit_theme_json();
		$filtered = apply_filters( 'wp_theme_json_data_theme', $theme_json );

		// Assert.
		$this->assertArrayNotHasKey( 'settings', $filtered->get_data() );
		init_theme_json();
	}
}
