<?php
/**
 * Tests for register_license_key_setting().
 *
 * @package OutletPro
 */

use function OutletPro\register_license_key_setting;
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

	public function test_sanitize_callback_is_sanitize_text_field(): void {
		// Arrange.
		unregister_setting( LICENSE_PAGE_SLUG, LICENSE_KEY_OPTION );

		// Act.
		register_license_key_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertSame( 'sanitize_text_field', $settings[ LICENSE_KEY_OPTION ]['sanitize_callback'] );
	}
}
