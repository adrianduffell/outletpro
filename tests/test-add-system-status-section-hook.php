<?php
/**
 * Test the add_system_status_section_hook function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_system_status_section_hook;
use function WC_Clearance\register_clearance_status_taxonomy;

class Test_Add_System_Status_Section extends WP_UnitTestCase {

	public function test_output_contains_section_heading(): void {
		// Arrange.
		register_clearance_status_taxonomy();

		// Expect.
		$this->expectOutputRegex( '/Clearance Section/' );

		// Act.
		add_system_status_section_hook();
	}

	public function test_table_has_correct_css_class(): void {
		// Arrange.
		register_clearance_status_taxonomy();

		// Expect.
		$this->expectOutputRegex( '/<table[^>]*class="(?=[^"]*\bwc_status_table\b)(?=[^"]*\bwidefat\b)[^"]*"/' );

		// Act.
		add_system_status_section_hook();
	}

	public function test_table_has_thead_and_tbody(): void {
		// Arrange.
		register_clearance_status_taxonomy();

		// Expect.
		$this->expectOutputRegex( '/<table[^>]*>.*?<thead>.*?<\/thead>.*?<tbody>.*?<\/tbody>.*?<\/table>/s' );

		// Act.
		add_system_status_section_hook();
	}
}
