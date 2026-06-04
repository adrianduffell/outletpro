<?php
/**
 * Test the register_outlet_badge_border_width_setting function.
 *
 * @package OutletPro
 */

use function OutletPro\register_outlet_badge_border_width_setting;
use const OutletPro\OUTLET_BADGE_BORDER_WIDTH_OPTION;

class Test_Register_Outlet_Badge_Border_Width_Setting extends WP_UnitTestCase {

	public function test_registers_outlet_badge_border_width_setting(): void {
		// Arrange.
		unregister_setting( 'wc_outlet', OUTLET_BADGE_BORDER_WIDTH_OPTION );

		// Act.
		register_outlet_badge_border_width_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertArrayHasKey( OUTLET_BADGE_BORDER_WIDTH_OPTION, $settings );
	}

	public function test_setting_type_is_string(): void {
		// Arrange.
		unregister_setting( 'wc_outlet', OUTLET_BADGE_BORDER_WIDTH_OPTION );

		// Act.
		register_outlet_badge_border_width_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertSame( 'string', $settings[ OUTLET_BADGE_BORDER_WIDTH_OPTION ]['type'] );
	}

	public function test_setting_is_shown_in_rest(): void {
		// Arrange.
		unregister_setting( 'wc_outlet', OUTLET_BADGE_BORDER_WIDTH_OPTION );
		register_outlet_badge_border_width_setting();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Act.
		$request  = new WP_REST_Request( 'GET', '/wp/v2/settings' );
		$response = rest_do_request( $request );

		// Assert.
		$this->assertArrayHasKey( OUTLET_BADGE_BORDER_WIDTH_OPTION, $response->get_data() );
	}

	public function test_setting_can_be_updated_via_rest(): void {
		// Arrange.
		unregister_setting( 'wc_outlet', OUTLET_BADGE_BORDER_WIDTH_OPTION );
		register_outlet_badge_border_width_setting();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Act.
		$request = new WP_REST_Request( 'POST', '/wp/v2/settings' );
		$request->set_param( OUTLET_BADGE_BORDER_WIDTH_OPTION, '1px' );
		$response = rest_do_request( $request );
		$data     = $response->get_data();

		// Assert.
		$this->assertSame( '1px', $data[ OUTLET_BADGE_BORDER_WIDTH_OPTION ] );
		$this->assertSame( '1px', get_option( OUTLET_BADGE_BORDER_WIDTH_OPTION ) );
	}
}
