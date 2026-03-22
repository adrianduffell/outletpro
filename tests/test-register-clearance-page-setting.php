<?php
/**
 * Test the register_clearance_page_setting function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\register_clearance_page_setting;
use const WC_Clearance\CLEARANCE_PAGE_OPTION;

class Test_Register_Clearance_Page_Setting extends WP_UnitTestCase {

	public function test_registers_clearance_page_id_setting(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_PAGE_OPTION );

		// Act.
		register_clearance_page_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertArrayHasKey( CLEARANCE_PAGE_OPTION, $settings );
	}

	public function test_setting_type_is_integer(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_PAGE_OPTION );

		// Act.
		register_clearance_page_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertSame( 'integer', $settings[ CLEARANCE_PAGE_OPTION ]['type'] );
	}

	public function test_setting_is_shown_in_rest(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_PAGE_OPTION );

		// Act.
		register_clearance_page_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertNotFalse( $settings[ CLEARANCE_PAGE_OPTION ]['show_in_rest'] );
	}

	public function test_setting_rest_schema_type_is_integer(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_PAGE_OPTION );

		// Act.
		register_clearance_page_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertSame( 'integer', $settings[ CLEARANCE_PAGE_OPTION ]['show_in_rest']['schema']['type'] );
	}

	public function test_setting_rest_schema_minimum_is_one(): void {
		// Arrange.
		unregister_setting( 'wc_clearance', CLEARANCE_PAGE_OPTION );

		// Act.
		register_clearance_page_setting();

		// Assert.
		$settings = get_registered_settings();
		$this->assertSame( 1, $settings[ CLEARANCE_PAGE_OPTION ]['show_in_rest']['schema']['minimum'] );
	}
}
