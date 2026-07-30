<?php
/**
 * Tests for invalidate_license_cache_hook().
 *
 * @package OutletPro
 * @group license
 */

use function OutletPro\deinit_license_settings;
use function OutletPro\has_license;
use function OutletPro\init_license_settings;
use const OutletPro\LICENSE_KEY_OPTION;

class Test_Invalidate_License_Cache_Hook extends WP_UnitTestCase {

	public function test_invalidates_transient_when_license_key_is_added(): void {
		// Arrange.
		deinit_license_settings();
		init_license_settings();
		delete_option( LICENSE_KEY_OPTION );
		$this->assertFalse( has_license() );

		// Act.
		update_option( LICENSE_KEY_OPTION, 'new-license-key' );

		// Assert.
		$this->assertTrue( has_license() );
	}

	public function test_invalidates_transient_when_license_key_is_updated(): void {
		// Arrange.
		deinit_license_settings();
		init_license_settings();
		update_option( LICENSE_KEY_OPTION, '0' );
		$this->assertFalse( has_license() );

		// Act.
		update_option( LICENSE_KEY_OPTION, 'new-license-key' );

		// Assert.
		$this->assertTrue( has_license() );
	}
}
