<?php
/**
 * Tests for invalidate_license_cache_hook().
 *
 * @package OutletPro
 */

use function OutletPro\deinit_license;
use function OutletPro\init_license;
use const OutletPro\HAS_LICENSE_TRANSIENT;
use const OutletPro\LICENSE_KEY_OPTION;

class Test_Invalidate_License_Cache_Hook extends WP_UnitTestCase {

	public function test_deletes_transient_when_license_key_is_updated(): void {
		// Arrange.
		init_license();
		set_transient( HAS_LICENSE_TRANSIENT, 'yes', WEEK_IN_SECONDS );

		// Act.
		update_option( LICENSE_KEY_OPTION, 'new-license-key' );

		// Assert.
		$this->assertFalse( get_transient( HAS_LICENSE_TRANSIENT ) );

		// Cleanup.
		deinit_license();
	}

	public function test_does_not_delete_transient_when_hook_is_not_registered(): void {
		// Arrange.
		deinit_license();
		set_transient( HAS_LICENSE_TRANSIENT, 'yes', WEEK_IN_SECONDS );

		// Act.
		update_option( LICENSE_KEY_OPTION, 'new-license-key' );

		// Assert.
		$this->assertSame( 'yes', get_transient( HAS_LICENSE_TRANSIENT ) );
	}
}
