<?php
/**
 * Test the register_clearance_badge_scale_setting function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\register_clearance_badge_scale_setting;
use const WC_Clearance\CLEARANCE_BADGE_SCALE_OPTION;

class Test_Register_Clearance_Badge_Scale_Setting extends WP_UnitTestCase {

	public function test_registers_clearance_badge_scale_setting(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_BADGE_SCALE_OPTION );

		// Act.
		register_clearance_badge_scale_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertArrayHasKey( CLEARANCE_BADGE_SCALE_OPTION, $settings );
	}

	public function test_setting_type_is_integer(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_BADGE_SCALE_OPTION );

		// Act.
		register_clearance_badge_scale_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertSame( 'integer', $settings[ CLEARANCE_BADGE_SCALE_OPTION ]['type'] );
	}

	public function test_setting_sanitize_callback_uses_unsigned_integer_sanitizer(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_BADGE_SCALE_OPTION );

		// Act.
		register_clearance_badge_scale_setting();
		$settings = get_registered_settings();
		$callback = $settings[ CLEARANCE_BADGE_SCALE_OPTION ]['sanitize_callback'];

		// Assert.
		$this->assertSame( 'WC_Clearance\sanitize_unsigned_integer', $callback );
		$this->assertSame( 140, $callback( '140' ) );
		$this->assertNull( $callback( '' ) );
		$this->assertNull( $callback( 'unexpected' ) );
	}

	public function test_setting_default_is_null(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_BADGE_SCALE_OPTION );
		delete_option( CLEARANCE_BADGE_SCALE_OPTION );
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Act.
		register_clearance_badge_scale_setting();
		$request  = new WP_REST_Request( 'GET', '/wp/v2/settings' );
		$response = rest_do_request( $request );
		$data     = $response->get_data();

		// Assert.
		$this->assertArrayHasKey( CLEARANCE_BADGE_SCALE_OPTION, $data );
		$this->assertNull( $data[ CLEARANCE_BADGE_SCALE_OPTION ] );
	}

	public function test_setting_is_shown_in_rest(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_BADGE_SCALE_OPTION );
		register_clearance_badge_scale_setting();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Act.
		$request  = new WP_REST_Request( 'GET', '/wp/v2/settings' );
		$response = rest_do_request( $request );

		// Assert.
		$this->assertArrayHasKey( CLEARANCE_BADGE_SCALE_OPTION, $response->get_data() );
	}

	public function test_setting_can_be_updated_via_rest_with_valid_value(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_BADGE_SCALE_OPTION );
		register_clearance_badge_scale_setting();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Act.
		$request = new WP_REST_Request( 'POST', '/wp/v2/settings' );
		$request->set_param( CLEARANCE_BADGE_SCALE_OPTION, 140 );
		$response = rest_do_request( $request );
		$data     = $response->get_data();

		// Assert.
		$this->assertSame( 140, $data[ CLEARANCE_BADGE_SCALE_OPTION ] );
		$this->assertSame( 140, get_option( CLEARANCE_BADGE_SCALE_OPTION ) );
	}

	public function test_setting_rest_schema_minimum_is_zero(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_BADGE_SCALE_OPTION );
		register_clearance_badge_scale_setting();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Act.
		$request = new WP_REST_Request( 'POST', '/wp/v2/settings' );
		$request->set_param( CLEARANCE_BADGE_SCALE_OPTION, -1 );
		$response = rest_do_request( $request );

		// Assert.
		$this->assertSame( 400, $response->get_status() );
	}
}
