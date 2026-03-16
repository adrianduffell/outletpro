<?php
/**
 * Test the setup_task_action_url function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\create_clearance_page;
use function WC_Clearance\get_clearance_page_id;
use function WC_Clearance\setup_task_action_url;

use const WC_Clearance\CLEARANCE_PAGE_OPTION;

class Test_Setup_Task_Action_Url extends WP_UnitTestCase {

	public function test_returns_edit_url_for_clearance_page(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );
		create_clearance_page();
		$page_id = get_clearance_page_id();

		// Act.
		$url = setup_task_action_url();

		// Assert.
		$this->assertSame( admin_url( 'post.php?post=' . $page_id . '&action=edit' ), $url );
	}

	public function test_returns_empty_string_when_no_clearance_page(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );

		// Act.
		$url = setup_task_action_url();

		// Assert.
		$this->assertSame( '', $url );
	}
}
