<?php
/**
 * Test the setup_task_is_complete function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\create_clearance_page;
use function WC_Clearance\get_clearance_page_id;
use function WC_Clearance\setup_task_is_complete;
use const WC_Clearance\CLEARANCE_PAGE_OPTION;

class Test_Setup_Task_Is_Complete extends WP_UnitTestCase {

	public function test_is_complete_returns_false_when_not_published(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );
		create_clearance_page();
		$page_id = get_clearance_page_id();
		wp_update_post(
			array(
				'ID'          => $page_id,
				'post_status' => 'draft',
			)
		);

		// Act.
		$result = setup_task_is_complete();

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_is_complete_returns_true_when_published(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );
		create_clearance_page();
		$page_id = get_clearance_page_id();
		wp_update_post(
			array(
				'ID'          => $page_id,
				'post_status' => 'publish',
			)
		);

		// Act.
		$result = setup_task_is_complete();

		// Assert.
		$this->assertTrue( $result );
	}
}
