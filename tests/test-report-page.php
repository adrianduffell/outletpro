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

	public function test_page_id_is_not_found_when_option_points_to_nonexistent_page(): void {
		// Arrange.
		update_option( CLEARANCE_PAGE_OPTION, 999999 );

		// Act.
		$result = report_page();

		// Assert.
		[ , $value ] = $result['clearance-page-id'];
		$this->assertSame( 'Not found', $value );
	}

	public function test_page_id_and_status_shown_for_draft_page(): void {
		// Arrange.
		$page_id = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => 'draft',
				'post_title'  => 'Clearance',
			)
		);
		update_option( CLEARANCE_PAGE_OPTION, $page_id );

		// Act.
		$result = report_page();

		// Assert.
		[ , $value ] = $result['clearance-page-id'];
		$this->assertSame( "$page_id (Draft)", $value );
	}

	public function test_page_id_and_status_shown_for_published_page(): void {
		// Arrange.
		$page_id = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'Clearance',
			)
		);
		update_option( CLEARANCE_PAGE_OPTION, $page_id );

		// Act.
		$result = report_page();

		// Assert.
		[ , $value ] = $result['clearance-page-id'];
		$this->assertSame( "$page_id (Published)", $value );
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
