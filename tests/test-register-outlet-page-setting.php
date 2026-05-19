<?php
/**
 * Test the register_outlet_page_setting function.
 *
 * @package WC_Outlet
 */

use function WC_Outlet\register_outlet_page_setting;
use const WC_Outlet\OUTLET_PAGE_OPTION;

class Test_Register_Outlet_Page_Setting extends WP_UnitTestCase {

	public function test_registers_outlet_page_id_setting(): void {
		// Arrange.
		unregister_setting( 'wc_outlet', OUTLET_PAGE_OPTION );

		// Act.
		register_outlet_page_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertArrayHasKey( OUTLET_PAGE_OPTION, $settings );
	}

	public function test_setting_type_is_integer(): void {
		// Arrange.
		unregister_setting( 'wc_outlet', OUTLET_PAGE_OPTION );

		// Act.
		register_outlet_page_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertSame( 'integer', $settings[ OUTLET_PAGE_OPTION ]['type'] );
	}

	public function test_setting_is_shown_in_rest(): void {
		// Arrange.
		unregister_setting( 'wc_outlet', OUTLET_PAGE_OPTION );
		register_outlet_page_setting();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Act.
		$request  = new WP_REST_Request( 'GET', '/wp/v2/settings' );
		$response = rest_do_request( $request );

		// Assert.
		$this->assertArrayHasKey( OUTLET_PAGE_OPTION, $response->get_data() );
	}

	public function test_setting_rest_schema_type_is_integer(): void {
		// Arrange.
		unregister_setting( 'wc_outlet', OUTLET_PAGE_OPTION );
		register_outlet_page_setting();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		$page_id = $this->factory->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);

		// Act.
		$request = new WP_REST_Request( 'POST', '/wp/v2/settings' );
		$request->set_param( OUTLET_PAGE_OPTION, $page_id );
		$response = rest_do_request( $request );

		// Assert.
		$this->assertIsInt( $response->get_data()[ OUTLET_PAGE_OPTION ] );
	}

	public function test_setting_rest_schema_minimum_is_one(): void {
		// Arrange.
		unregister_setting( 'wc_outlet', OUTLET_PAGE_OPTION );
		register_outlet_page_setting();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Act.
		$request = new WP_REST_Request( 'POST', '/wp/v2/settings' );
		$request->set_param( OUTLET_PAGE_OPTION, 0 );
		$response = rest_do_request( $request );

		// Assert.
		$this->assertSame( 400, $response->get_status() );
	}
}
