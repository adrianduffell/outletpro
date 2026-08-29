<?php
/**
 * Tests for invalidate_license_transients().
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\invalidate_license_transients;
use const OutletPro\LICENSE_EXPIRY_TRANSIENT;
use const OutletPro\LICENSE_NAME_TRANSIENT;
use const OutletPro\LICENSE_STATUS_TRANSIENT;

class Test_Invalidate_License_Transients extends WP_UnitTestCase {

	public function test_invalidates_license_status_transient(): void {
		// Arrange.
		set_transient( LICENSE_STATUS_TRANSIENT, 'active' );

		// Act.
		invalidate_license_transients();

		// Assert.
		$this->assertFalse( get_transient( LICENSE_STATUS_TRANSIENT ) );
	}

	public function test_invalidates_license_name_transient(): void {
		// Arrange.
		set_transient( LICENSE_NAME_TRANSIENT, 'License' );

		// Act.
		invalidate_license_transients();

		// Assert.
		$this->assertFalse( get_transient( LICENSE_NAME_TRANSIENT ) );
	}

	public function test_invalidates_license_expiry_transient(): void {
		// Arrange.
		set_transient( LICENSE_EXPIRY_TRANSIENT, array( false ) );

		// Act.
		invalidate_license_transients();

		// Assert.
		$this->assertFalse( get_transient( LICENSE_EXPIRY_TRANSIENT ) );
	}
}
