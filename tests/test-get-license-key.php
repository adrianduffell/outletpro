<?php
/**
 * Test the get_license_key function.
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_license_settings;
use function OutletPro\get_license_key;
use const OutletPro\LICENSE_KEY_OPTION;

class Test_Get_License_Key extends WP_UnitTestCase {

	public function test_returns_license_key(): void {
		// Arrange.
		deinit_license_settings();
		update_option( LICENSE_KEY_OPTION, 'license-key' );

		// Act.
		$license_key = get_license_key();

		// Assert.
		$this->assertSame( 'license-key', $license_key );
	}

	public function test_returns_null_when_license_key_is_absent(): void {
		// Arrange.
		deinit_license_settings();
		delete_option( LICENSE_KEY_OPTION );

		// Act.
		$license_key = get_license_key();

		// Assert.
		$this->assertNull( $license_key );
	}

	public function test_returns_null_when_license_key_value_is_null(): void {
		// Arrange.
		deinit_license_settings();
		add_filter(
			'pre_option_' . LICENSE_KEY_OPTION,
			fn(): ?string => null
		);

		// Act.
		$license_key = get_license_key();

		// Assert.
		$this->assertNull( $license_key );
	}

	public function test_returns_blank_license_key(): void {
		// Arrange.
		deinit_license_settings();
		update_option( LICENSE_KEY_OPTION, '' );

		// Act.
		$license_key = get_license_key();

		// Assert.
		$this->assertSame( '', $license_key );
	}

	public function test_returns_short_license_key(): void {
		// Arrange.
		deinit_license_settings();
		update_option( LICENSE_KEY_OPTION, 'a' );

		// Act.
		$license_key = get_license_key();

		// Assert.
		$this->assertSame( 'a', $license_key );
	}

	public function test_throws_when_license_key_is_not_a_string(): void {
		// Arrange.
		deinit_license_settings();
		add_filter(
			'pre_option_' . LICENSE_KEY_OPTION,
			fn(): array => array( 'license-key' )
		);

		// Expect.
		$this->expectException( \UnexpectedValueException::class );
		$this->expectExceptionMessage( 'Invalid license key option value.' );

		// Act.
		get_license_key();
	}
}
