<?php
/**
 * Test the report_page function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\report_page;
use const WC_Clearance\CLEARANCE_PAGE_OPTION;

class Test_Report_Page extends WP_UnitTestCase {

	public function test_page_id_is_not_found_when_option_is_missing(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );

		// Act.
		$result = report_page();

		// Assert.
		[ , $value ] = $result['clearance-page-id'];
		$this->assertSame( 'Not found', $value );
	}

	public function test_page_id_is_not_found_when_option_is_zero(): void {
		// Arrange.
		update_option( CLEARANCE_PAGE_OPTION, 0 );

		// Act.
		$result = report_page();

		// Assert.
		[ , $value ] = $result['clearance-page-id'];
		$this->assertSame( 'Not found', $value );
	}

	public function test_page_id_is_returned_when_option_is_set(): void {
		// Arrange.
		update_option( CLEARANCE_PAGE_OPTION, 42 );

		// Act.
		$result = report_page();

		// Assert.
		[ , $value ] = $result['clearance-page-id'];
		$this->assertSame( 42, $value );
	}

	public function test_label_is_page_id(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );

		// Act.
		$result = report_page();

		// Assert.
		[ $label ] = $result['clearance-page-id'];
		$this->assertSame( 'Page ID', $label );
	}
}
