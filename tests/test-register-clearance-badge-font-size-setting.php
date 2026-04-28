<?php
/**
 * Test the register_clearance_badge_font_size_setting function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\register_clearance_badge_font_size_setting;
use const WC_Clearance\CLEARANCE_BADGE_FONT_SIZE_OPTION;

class Test_Register_Clearance_Badge_Font_Size_Setting extends WP_UnitTestCase {

	public function test_registers_clearance_badge_font_size_setting(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_BADGE_FONT_SIZE_OPTION );

		// Act.
		register_clearance_badge_font_size_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertArrayHasKey( CLEARANCE_BADGE_FONT_SIZE_OPTION, $settings );
	}

	public function test_setting_type_is_string(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_BADGE_FONT_SIZE_OPTION );

		// Act.
		register_clearance_badge_font_size_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertSame( 'string', $settings[ CLEARANCE_BADGE_FONT_SIZE_OPTION ]['type'] );
	}

	public function test_setting_default_is_empty_string(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_BADGE_FONT_SIZE_OPTION );

		// Act.
		register_clearance_badge_font_size_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertSame( '', $settings[ CLEARANCE_BADGE_FONT_SIZE_OPTION ]['default'] );
	}

	public function test_setting_uses_css_value_sanitize_callback(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_BADGE_FONT_SIZE_OPTION );

		// Act.
		register_clearance_badge_font_size_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertSame(
			'WC_Clearance\\wc_clearance_sanitize_css_value',
			$settings[ CLEARANCE_BADGE_FONT_SIZE_OPTION ]['sanitize_callback']
		);
	}

	public function test_setting_is_shown_in_rest(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_BADGE_FONT_SIZE_OPTION );
		register_clearance_badge_font_size_setting();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Act.
		$request  = new WP_REST_Request( 'GET', '/wp/v2/settings' );
		$response = rest_do_request( $request );

		// Assert.
		$this->assertArrayHasKey( CLEARANCE_BADGE_FONT_SIZE_OPTION, $response->get_data() );
	}

	public function test_setting_can_be_updated_via_rest_with_css_length_value(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_BADGE_FONT_SIZE_OPTION );
		register_clearance_badge_font_size_setting();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Act.
		$request = new WP_REST_Request( 'POST', '/wp/v2/settings' );
		$request->set_param( CLEARANCE_BADGE_FONT_SIZE_OPTION, '0.875rem' );
		$response = rest_do_request( $request );
		$data     = $response->get_data();

		// Assert.
		$this->assertSame( '0.875rem', $data[ CLEARANCE_BADGE_FONT_SIZE_OPTION ] );
		$this->assertSame( '0.875rem', get_option( CLEARANCE_BADGE_FONT_SIZE_OPTION ) );
	}
}
