<?php
/**
 * Test the register_clearance_message_setting function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\register_clearance_message_setting;
use const WC_Clearance\CLEARANCE_MESSAGE_OPTION;

class Test_Register_Clearance_Message_Setting extends WP_UnitTestCase {

	public function test_registers_clearance_message_setting(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_MESSAGE_OPTION );

		// Act.
		register_clearance_message_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertArrayHasKey( CLEARANCE_MESSAGE_OPTION, $settings );
	}

	public function test_setting_type_is_string(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_MESSAGE_OPTION );

		// Act.
		register_clearance_message_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertSame( 'string', $settings[ CLEARANCE_MESSAGE_OPTION ]['type'] );
	}

	public function test_setting_is_shown_in_rest(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_MESSAGE_OPTION );
		register_clearance_message_setting();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Act.
		$request  = new WP_REST_Request( 'GET', '/wp/v2/settings' );
		$response = rest_do_request( $request );

		// Assert.
		$this->assertArrayHasKey( CLEARANCE_MESSAGE_OPTION, $response->get_data() );
	}
}
