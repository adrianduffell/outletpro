<?php
/**
 * Test the init_license function.
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_license_settings;
use function OutletPro\init_license_settings;

class Test_Init_License_Settings extends WP_UnitTestCase {

	public function test_init_license_settings_registers_setting(): void {
		// Arrange.
		deinit_license_settings();

		// Act.
		init_license_settings();

		// Assert.
		$this->assertArrayHasKey( 'outletpro_license_key', get_registered_settings() );
	}
}
