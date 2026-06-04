<?php
/**
 * Test the register_outlet_badge_border_color_setting function.
 *
 * @package OutletPro
 */

use function OutletPro\register_outlet_badge_border_color_setting;
use const OutletPro\OUTLET_BADGE_BORDER_COLOR_OPTION;

class Test_Register_Outlet_Badge_Border_Color_Setting extends WP_UnitTestCase {

	public function test_registers_outlet_badge_border_color_setting(): void {
		// Arrange.
		unregister_setting( 'wc_outlet', OUTLET_BADGE_BORDER_COLOR_OPTION );

		// Act.
		register_outlet_badge_border_color_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertArrayHasKey( OUTLET_BADGE_BORDER_COLOR_OPTION, $settings );
	}

	public function test_setting_type_is_string(): void {
		// Arrange.
		unregister_setting( 'wc_outlet', OUTLET_BADGE_BORDER_COLOR_OPTION );

		// Act.
		register_outlet_badge_border_color_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertSame( 'string', $settings[ OUTLET_BADGE_BORDER_COLOR_OPTION ]['type'] );
	}

	public function test_setting_is_shown_in_rest(): void {
		// Arrange.
		unregister_setting( 'wc_outlet', OUTLET_BADGE_BORDER_COLOR_OPTION );
		register_outlet_badge_border_color_setting();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Act.
		$request  = new WP_REST_Request( 'GET', '/wp/v2/settings' );
		$response = rest_do_request( $request );

		// Assert.
		$this->assertArrayHasKey( OUTLET_BADGE_BORDER_COLOR_OPTION, $response->get_data() );
	}

	public function test_setting_can_be_updated_via_rest(): void {
		// Arrange.
		unregister_setting( 'wc_outlet', OUTLET_BADGE_BORDER_COLOR_OPTION );
		register_outlet_badge_border_color_setting();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Act.
		$request = new WP_REST_Request( 'POST', '/wp/v2/settings' );
		$request->set_param( OUTLET_BADGE_BORDER_COLOR_OPTION, '#000000' );
		$response = rest_do_request( $request );
		$data     = $response->get_data();

		// Assert.
		$this->assertSame( '#000000', $data[ OUTLET_BADGE_BORDER_COLOR_OPTION ] );
		$this->assertSame( '#000000', get_option( OUTLET_BADGE_BORDER_COLOR_OPTION ) );
	}
}
