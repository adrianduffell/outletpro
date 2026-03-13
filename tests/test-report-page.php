<?php
/**
 * Test the report_page function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\create_clearance_page;
use function WC_Clearance\report_page;
use const WC_Clearance\CLEARANCE_PAGE_OPTION;

class Test_Report_Page extends WP_UnitTestCase {

	public function test_page_is_null_when_option_is_not_set(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );

		// Act.
		$result = report_page();

		// Assert.
		$this->assertNull( $result['clearance-section-page'][1] );
	}

	public function test_page_is_wp_post_when_page_exists(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );
		create_clearance_page();

		// Act.
		$result = report_page();

		// Assert.
		$this->assertInstanceOf( WP_Post::class, $result['clearance-section-page'][1] );
	}

	public function test_page_has_draft_status_after_creation(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );
		create_clearance_page();

		// Act.
		$result = report_page();

		// Assert.
		$this->assertSame( 'draft', $result['clearance-section-page'][1]->post_status );
	}

	public function test_page_reflects_updated_status(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );
		create_clearance_page();
		$page_id = (int) get_option( CLEARANCE_PAGE_OPTION );
		wp_update_post(
			array(
				'ID'          => $page_id,
				'post_status' => 'publish',
			)
		);

		// Act.
		$result = report_page();

		// Assert.
		$this->assertSame( 'publish', $result['clearance-section-page'][1]->post_status );
	}

	public function test_page_is_null_when_option_is_corrupted(): void {
		// Arrange.
		update_option( CLEARANCE_PAGE_OPTION, 'not-a-number' );

		// Act.
		$result = report_page();

		// Assert.
		$this->assertNull( $result['clearance-section-page'][1] );
	}
}
