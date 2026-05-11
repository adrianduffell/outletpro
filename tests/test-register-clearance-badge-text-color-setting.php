<?php
/**
 * Test the register_clearance_badge_text_color_setting function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\register_clearance_badge_text_color_setting;
use const WC_Clearance\CLEARANCE_BADGE_TEXT_COLOR_OPTION;

class Test_Register_Clearance_Badge_Text_Color_Setting extends WP_UnitTestCase {

	public function test_registers_clearance_badge_text_color_setting(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_BADGE_TEXT_COLOR_OPTION );

		// Act.
		register_clearance_badge_text_color_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertArrayHasKey( CLEARANCE_BADGE_TEXT_COLOR_OPTION, $settings );
	}

	public function test_setting_type_is_string(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_BADGE_TEXT_COLOR_OPTION );

		// Act.
		register_clearance_badge_text_color_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertSame( 'string', $settings[ CLEARANCE_BADGE_TEXT_COLOR_OPTION ]['type'] );
	}

	public function test_setting_is_shown_in_rest(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_BADGE_TEXT_COLOR_OPTION );
		register_clearance_badge_text_color_setting();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Act.
		$request  = new WP_REST_Request( 'GET', '/wp/v2/settings' );
		$response = rest_do_request( $request );

		// Assert.
		$this->assertArrayHasKey( CLEARANCE_BADGE_TEXT_COLOR_OPTION, $response->get_data() );
	}

	public function test_setting_default_is_empty_string(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_BADGE_TEXT_COLOR_OPTION );
		register_clearance_badge_text_color_setting();
		delete_option( CLEARANCE_BADGE_TEXT_COLOR_OPTION );
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Act.
		$request  = new WP_REST_Request( 'GET', '/wp/v2/settings' );
		$response = rest_do_request( $request );
		$data     = $response->get_data();

		// Assert.
		$this->assertSame( '', $data[ CLEARANCE_BADGE_TEXT_COLOR_OPTION ] );
	}

	public function test_setting_can_be_updated_via_rest(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_BADGE_TEXT_COLOR_OPTION );
		register_clearance_badge_text_color_setting();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Act.
		$request = new WP_REST_Request( 'POST', '/wp/v2/settings' );
		$request->set_param( CLEARANCE_BADGE_TEXT_COLOR_OPTION, '#FF0000' );
		$response = rest_do_request( $request );
		$data     = $response->get_data();

		// Assert.
		$this->assertSame( '#FF0000', $data[ CLEARANCE_BADGE_TEXT_COLOR_OPTION ] );
		$this->assertSame( '#FF0000', get_option( CLEARANCE_BADGE_TEXT_COLOR_OPTION ) );
	}
}
