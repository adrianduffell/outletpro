<?php
/**
 * Test the register_clearance_badge_label_setting function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\register_clearance_badge_label_setting;
use const WC_Clearance\CLEARANCE_BADGE_LABEL_OPTION;

class Test_Register_Clearance_Badge_Label_Setting extends WP_UnitTestCase {

	public function test_registers_clearance_badge_label_setting(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_BADGE_LABEL_OPTION );

		// Act.
		register_clearance_badge_label_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertArrayHasKey( CLEARANCE_BADGE_LABEL_OPTION, $settings );
	}

	public function test_setting_type_is_string(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_BADGE_LABEL_OPTION );

		// Act.
		register_clearance_badge_label_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertSame( 'string', $settings[ CLEARANCE_BADGE_LABEL_OPTION ]['type'] );
	}

	public function test_setting_is_shown_in_rest(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_BADGE_LABEL_OPTION );
		register_clearance_badge_label_setting();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Act.
		$request  = new WP_REST_Request( 'GET', '/wp/v2/settings' );
		$response = rest_do_request( $request );

		// Assert.
		$this->assertArrayHasKey( CLEARANCE_BADGE_LABEL_OPTION, $response->get_data() );
	}

	public function test_setting_default_is_clearance(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_BADGE_LABEL_OPTION );

		// Act.
		register_clearance_badge_label_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertSame( 'Clearance', $settings[ CLEARANCE_BADGE_LABEL_OPTION ]['default'] );
	}
}
