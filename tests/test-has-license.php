<?php
/**
 * Test the has_license function.
 *
 * @package OutletPro
 * @group License
 */

use function OutletPro\has_license;
use const OutletPro\HAS_LICENSE_TRANSIENT;
use const OutletPro\LICENSE_KEY_OPTION;

class Test_Has_License extends WP_UnitTestCase {

	public function test_returns_true_and_caches_valid_license(): void {
		// Arrange.
		delete_option( LICENSE_KEY_OPTION );
		delete_transient( HAS_LICENSE_TRANSIENT );
		update_option( LICENSE_KEY_OPTION, 'ab' );

		// Act.
		$result = has_license();

		// Assert.
		$this->assertTrue( $result );
		$this->assertSame( 'yes', get_transient( HAS_LICENSE_TRANSIENT ) );
	}

	public function test_returns_false_and_caches_invalid_license(): void {
		// Arrange.
		delete_option( LICENSE_KEY_OPTION );
		delete_transient( HAS_LICENSE_TRANSIENT );
		update_option( LICENSE_KEY_OPTION, 'a' );

		// Act.
		$result = has_license();

		// Assert.
		$this->assertFalse( $result );
		$this->assertSame( 'no', get_transient( HAS_LICENSE_TRANSIENT ) );
	}

	public function test_returns_cached_true_value_without_revalidating(): void {
		// Arrange.
		delete_option( LICENSE_KEY_OPTION );
		delete_transient( HAS_LICENSE_TRANSIENT );
		update_option( LICENSE_KEY_OPTION, 'a' );
		set_transient( HAS_LICENSE_TRANSIENT, 'yes', WEEK_IN_SECONDS );

		// Act.
		$result = has_license();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_returns_cached_false_value_without_revalidating(): void {
		// Arrange.
		delete_option( LICENSE_KEY_OPTION );
		delete_transient( HAS_LICENSE_TRANSIENT );
		update_option( LICENSE_KEY_OPTION, 'ab' );
		set_transient( HAS_LICENSE_TRANSIENT, 'no', WEEK_IN_SECONDS );

		// Act.
		$result = has_license();

		// Assert.
		$this->assertFalse( $result );
	}
}
