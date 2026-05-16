<?php
/**
 * Test the register_clearance_badge_min_font_size_setting function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\init_settings;
use const WC_Clearance\CLEARANCE_BADGE_MIN_FONT_SIZE_OPTION;

class Test_Register_Clearance_Badge_Min_Font_Size_Setting extends WP_UnitTestCase {

	public function test_setting_is_registered(): void {
		// Arrange.
		init_settings();

		// Act.
		$registered = get_registered_settings();

		// Assert.
		$this->assertArrayHasKey( CLEARANCE_BADGE_MIN_FONT_SIZE_OPTION, $registered );
	}

	public function test_setting_type_is_string(): void {
		// Arrange.
		init_settings();

		// Act.
		$registered = get_registered_settings();

		// Assert.
		$this->assertSame( 'string', $registered[ CLEARANCE_BADGE_MIN_FONT_SIZE_OPTION ]['type'] );
	}

	public function test_setting_default_is_empty_string(): void {
		// Arrange.
		init_settings();

		// Act.
		$registered = get_registered_settings();

		// Assert.
		$this->assertSame( '', $registered[ CLEARANCE_BADGE_MIN_FONT_SIZE_OPTION ]['default'] );
	}

	public function test_setting_is_shown_in_rest(): void {
		// Arrange.
		init_settings();

		// Act.
		$registered = get_registered_settings();

		// Assert.
		$this->assertNotFalse( $registered[ CLEARANCE_BADGE_MIN_FONT_SIZE_OPTION ]['show_in_rest'] );
	}

	public function test_sanitize_callback_strips_block_delimiters(): void {
		// Arrange.
		init_settings();

		// Act.
		$result = call_user_func(
			get_registered_settings()[ CLEARANCE_BADGE_MIN_FONT_SIZE_OPTION ]['sanitize_callback'],
			'10px; color: red'
		);

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_sanitize_callback_accepts_valid_css_length(): void {
		// Arrange.
		init_settings();

		// Act.
		$result = call_user_func(
			get_registered_settings()[ CLEARANCE_BADGE_MIN_FONT_SIZE_OPTION ]['sanitize_callback'],
			'1rem'
		);

		// Assert.
		$this->assertSame( '1rem', $result );
	}
}
