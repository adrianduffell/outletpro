<?php
/**
 * Tests for register_license_key_setting().
 *
 * @package OutletPro
 */

use function OutletPro\register_license_key_setting;
use function OutletPro\sanitize_license_key;
use const OutletPro\LICENSE_KEY_OPTION;
use const OutletPro\LICENSE_PAGE_SLUG;

class Test_Register_License_Key_Setting extends WP_UnitTestCase {

	public function test_registers_license_key_setting(): void {
		// Arrange.
		unregister_setting( LICENSE_PAGE_SLUG, LICENSE_KEY_OPTION );

		// Act.
		register_license_key_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertArrayHasKey( LICENSE_KEY_OPTION, $settings );
	}

	public function test_setting_type_is_string(): void {
		// Arrange.
		unregister_setting( LICENSE_PAGE_SLUG, LICENSE_KEY_OPTION );

		// Act.
		register_license_key_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertSame( 'string', $settings[ LICENSE_KEY_OPTION ]['type'] );
	}

	public function test_setting_default_is_empty_string(): void {
		// Arrange.
		unregister_setting( LICENSE_PAGE_SLUG, LICENSE_KEY_OPTION );

		// Act.
		register_license_key_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertSame( '', $settings[ LICENSE_KEY_OPTION ]['default'] );
	}

	public function test_sanitize_callback_rejects_invalid_key(): void {
		// Arrange.
		unregister_setting( LICENSE_PAGE_SLUG, LICENSE_KEY_OPTION );
		register_license_key_setting();

		// Act.
		$result = sanitize_license_key( 'invalid' );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_sanitize_callback_accepts_valid_key(): void {
		// Arrange.
		unregister_setting( LICENSE_PAGE_SLUG, LICENSE_KEY_OPTION );
		register_license_key_setting();

		// Act.
		$result = sanitize_license_key( 'ABCDEF1234567890' );

		// Assert.
		$this->assertSame( 'ABCDEF1234567890', $result );
	}
}
