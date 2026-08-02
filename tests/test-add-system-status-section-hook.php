<?php
/**
 * Test the add_system_status_section_hook function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\init_system_status;
use function OutletPro\register_outlet_status_taxonomy;

class Test_Add_System_Status_Section extends WP_UnitTestCase {

	public function test_output_contains_section_heading(): void {
		// Arrange.
		init_system_status();
		register_outlet_status_taxonomy();

		// Expect.
		$this->expectOutputRegex( '/Outlet/' );

		// Act.
		do_action( 'woocommerce_system_status_report' );
	}

	public function test_table_has_correct_css_class(): void {
		// Arrange.
		init_system_status();
		register_outlet_status_taxonomy();

		// Expect.
		$this->expectOutputRegex( '/<table[^>]*class="(?=[^"]*\bwc_status_table\b)(?=[^"]*\bwidefat\b)[^"]*"/' );

		// Act.
		do_action( 'woocommerce_system_status_report' );
	}

	public function test_table_has_thead_and_tbody(): void {
		// Arrange.
		init_system_status();
		register_outlet_status_taxonomy();

		// Expect.
		$this->expectOutputRegex( '/<table[^>]*>.*?<thead>.*?<\/thead>.*?<tbody>.*?<\/tbody>.*?<\/table>/s' );

		// Act.
		do_action( 'woocommerce_system_status_report' );
	}
}
