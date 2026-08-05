<?php
/**
 * Test the deinit_license_settings function.
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_license_settings;
use function OutletPro\init_license_settings;

class Test_Deinit_License_Settings extends WP_UnitTestCase {

	public function test_deinit_license_settings_unregisters_setting(): void {
		// Arrange.
		deinit_license_settings();
		init_license_settings();
		$this->assertArrayHasKey( 'outletpro_license_key', get_registered_settings() );

		// Act.
		deinit_license_settings();

		// Assert.
		$this->assertArrayNotHasKey( 'outletpro_license_key', get_registered_settings() );
	}
}
