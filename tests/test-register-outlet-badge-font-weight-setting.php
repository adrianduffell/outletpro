<?php
/**
 * Test the register_outlet_badge_font_weight_setting function.
 *
 * @package OutletPro
 */

use function OutletPro\register_outlet_badge_font_weight_setting;
use const OutletPro\OUTLET_BADGE_FONT_WEIGHT_OPTION;

class Test_Register_Outlet_Badge_Font_Weight_Setting extends WP_UnitTestCase {

	public function test_registers_outlet_badge_font_weight_setting(): void {
		// Arrange.
		unregister_setting( 'outletpro', OUTLET_BADGE_FONT_WEIGHT_OPTION );

		// Act.
		register_outlet_badge_font_weight_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertArrayHasKey( OUTLET_BADGE_FONT_WEIGHT_OPTION, $settings );
	}

	public function test_setting_type_is_string(): void {
		// Arrange.
		unregister_setting( 'outletpro', OUTLET_BADGE_FONT_WEIGHT_OPTION );

		// Act.
		register_outlet_badge_font_weight_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertSame( 'string', $settings[ OUTLET_BADGE_FONT_WEIGHT_OPTION ]['type'] );
	}

	public function test_setting_is_shown_in_rest(): void {
		// Arrange.
		unregister_setting( 'outletpro', OUTLET_BADGE_FONT_WEIGHT_OPTION );
		register_outlet_badge_font_weight_setting();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Act.
		$request  = new WP_REST_Request( 'GET', '/wp/v2/settings' );
		$response = rest_do_request( $request );

		// Assert.
		$this->assertArrayHasKey( OUTLET_BADGE_FONT_WEIGHT_OPTION, $response->get_data() );
	}

	public function test_setting_can_be_updated_via_rest(): void {
		// Arrange.
		unregister_setting( 'outletpro', OUTLET_BADGE_FONT_WEIGHT_OPTION );
		register_outlet_badge_font_weight_setting();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Act.
		$request = new WP_REST_Request( 'POST', '/wp/v2/settings' );
		$request->set_param( OUTLET_BADGE_FONT_WEIGHT_OPTION, '600' );
		$response = rest_do_request( $request );
		$data     = $response->get_data();

		// Assert.
		$this->assertSame( '600', $data[ OUTLET_BADGE_FONT_WEIGHT_OPTION ] );
		$this->assertSame( '600', get_option( OUTLET_BADGE_FONT_WEIGHT_OPTION ) );
	}
}
