<?php
/**
 * Tests for register_license_key_setting().
 *
 * @package OutletPro
 * @group License
 */

use function OutletPro\register_license_key_setting;
use const OutletPro\LICENSE_KEY_GROUP;
use const OutletPro\LICENSE_KEY_OPTION;

class Test_Register_License_Key_Setting extends WP_UnitTestCase {

	public function test_registers_license_key_setting(): void {
		// Arrange.
		unregister_setting( LICENSE_KEY_GROUP, LICENSE_KEY_OPTION );

		// Act.
		register_license_key_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertArrayHasKey( LICENSE_KEY_OPTION, $settings );
	}

	public function test_setting_type_is_string(): void {
		// Arrange.
		unregister_setting( LICENSE_KEY_GROUP, LICENSE_KEY_OPTION );

		// Act.
		register_license_key_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertSame( 'string', $settings[ LICENSE_KEY_OPTION ]['type'] );
	}

	public function test_setting_default_is_empty_string(): void {
		// Arrange.
		unregister_setting( LICENSE_KEY_GROUP, LICENSE_KEY_OPTION );

		// Act.
		register_license_key_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertSame( '', $settings[ LICENSE_KEY_OPTION ]['default'] );
	}

	public function test_sanitize_callback_is_sanitize_text_field(): void {
		// Arrange.
		unregister_setting( LICENSE_KEY_GROUP, LICENSE_KEY_OPTION );

		// Act.
		register_license_key_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertSame( 'sanitize_text_field', $settings[ LICENSE_KEY_OPTION ]['sanitize_callback'] );
	}

	public function test_setting_is_shown_in_rest(): void {
		// Arrange.
		unregister_setting( LICENSE_KEY_GROUP, LICENSE_KEY_OPTION );
		register_license_key_setting();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Act.
		$request  = new WP_REST_Request( 'GET', '/wp/v2/settings' );
		$response = rest_do_request( $request );

		// Assert.
		$this->assertArrayHasKey( LICENSE_KEY_OPTION, $response->get_data() );
	}

	public function test_setting_can_be_updated_via_rest(): void {
		// Arrange.
		unregister_setting( LICENSE_KEY_GROUP, LICENSE_KEY_OPTION );
		register_license_key_setting();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Act.
		$request = new WP_REST_Request( 'POST', '/wp/v2/settings' );
		$request->set_param( LICENSE_KEY_OPTION, 'ABCD-EFGH-IJKL-MNOP' );
		$response = rest_do_request( $request );
		$data     = $response->get_data();

		// Assert.
		$this->assertSame( 'ABCD-EFGH-IJKL-MNOP', $data[ LICENSE_KEY_OPTION ] );
		$this->assertSame( 'ABCD-EFGH-IJKL-MNOP', get_option( LICENSE_KEY_OPTION ) );
	}
}
