<?php
/**
 * Test the register_clearance_badge_density_setting function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\register_clearance_badge_density_setting;
use const WC_Clearance\CLEARANCE_BADGE_DENSITY_OPTION;

class Test_Register_Clearance_Badge_Density_Setting extends WP_UnitTestCase {

	public function test_registers_clearance_badge_density_setting(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_BADGE_DENSITY_OPTION );

		// Act.
		register_clearance_badge_density_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertArrayHasKey( CLEARANCE_BADGE_DENSITY_OPTION, $settings );
	}

	public function test_setting_type_is_integer(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_BADGE_DENSITY_OPTION );

		// Act.
		register_clearance_badge_density_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertSame( 'integer', $settings[ CLEARANCE_BADGE_DENSITY_OPTION ]['type'] );
	}

	public function test_setting_default_is_null_when_option_not_set(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_BADGE_DENSITY_OPTION );
		delete_option( CLEARANCE_BADGE_DENSITY_OPTION );
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Act.
		register_clearance_badge_density_setting();
		$request  = new WP_REST_Request( 'GET', '/wp/v2/settings' );
		$response = rest_do_request( $request );
		$data     = $response->get_data();

		// Assert.
		$this->assertArrayHasKey( CLEARANCE_BADGE_DENSITY_OPTION, $data );
		$this->assertNull( $data[ CLEARANCE_BADGE_DENSITY_OPTION ] );
	}

	public function test_setting_is_shown_in_rest(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_BADGE_DENSITY_OPTION );
		register_clearance_badge_density_setting();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Act.
		$request  = new WP_REST_Request( 'GET', '/wp/v2/settings' );
		$response = rest_do_request( $request );

		// Assert.
		$this->assertArrayHasKey( CLEARANCE_BADGE_DENSITY_OPTION, $response->get_data() );
	}

	public function test_setting_can_be_updated_via_rest_with_valid_value(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_BADGE_DENSITY_OPTION );
		register_clearance_badge_density_setting();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Act.
		$request = new WP_REST_Request( 'POST', '/wp/v2/settings' );
		$request->set_param( CLEARANCE_BADGE_DENSITY_OPTION, 40 );
		$response = rest_do_request( $request );
		$data     = $response->get_data();

		// Assert.
		$this->assertSame( 40, $data[ CLEARANCE_BADGE_DENSITY_OPTION ] );
		$this->assertSame( 40, get_option( CLEARANCE_BADGE_DENSITY_OPTION ) );
	}

	public function test_setting_rest_schema_minimum_is_zero(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_BADGE_DENSITY_OPTION );
		register_clearance_badge_density_setting();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Act.
		$request = new WP_REST_Request( 'POST', '/wp/v2/settings' );
		$request->set_param( CLEARANCE_BADGE_DENSITY_OPTION, -1 );
		$response = rest_do_request( $request );

		// Assert.
		$this->assertSame( 400, $response->get_status() );
	}
}
