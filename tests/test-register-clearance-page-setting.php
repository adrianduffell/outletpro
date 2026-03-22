<?php
/**
 * Test the init_settings function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\init_settings;
use const WC_Clearance\CLEARANCE_PAGE_OPTION;

class Test_Register_Clearance_Page_Setting extends WP_UnitTestCase {

	public function test_registers_clearance_page_id_setting(): void {
		// Arrange.

		// Act.
		init_settings();

		// Assert.
		$settings = get_registered_settings();
		$this->assertArrayHasKey( CLEARANCE_PAGE_OPTION, $settings );
	}

	public function test_setting_type_is_integer(): void {
		// Arrange.

		// Act.
		init_settings();

		// Assert.
		$settings = get_registered_settings();
		$this->assertSame( 'integer', $settings[ CLEARANCE_PAGE_OPTION ]['type'] );
	}

	public function test_setting_is_shown_in_rest(): void {
		// Arrange.

		// Act.
		init_settings();

		// Assert.
		$settings = get_registered_settings();
		$this->assertNotFalse( $settings[ CLEARANCE_PAGE_OPTION ]['show_in_rest'] );
	}

	public function test_setting_rest_schema_type_is_integer(): void {
		// Arrange.

		// Act.
		init_settings();

		// Assert.
		$settings = get_registered_settings();
		$this->assertSame( 'integer', $settings[ CLEARANCE_PAGE_OPTION ]['show_in_rest']['schema']['type'] );
	}

	public function test_setting_rest_schema_minimum_is_one(): void {
		// Arrange.

		// Act.
		init_settings();

		// Assert.
		$settings = get_registered_settings();
		$this->assertSame( 1, $settings[ CLEARANCE_PAGE_OPTION ]['show_in_rest']['schema']['minimum'] );
	}
}
