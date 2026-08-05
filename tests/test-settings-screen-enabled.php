<?php
/**
 * Tests for settings_screen_enabled().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\settings_screen_enabled;

class Test_Settings_Screen_Enabled extends WP_UnitTestCase {

	public function test_settings_enabled_returns_false_by_default(): void {
		// Act.
		$result = settings_screen_enabled();

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_settings_enabled_returns_true_when_filter_enables_it(): void {
		// Arrange.
		add_filter( 'outletpro_settings_screen_enabled', '__return_true' );

		// Act.
		$result = settings_screen_enabled();

		// Assert.
		$this->assertTrue( $result );
	}
}
