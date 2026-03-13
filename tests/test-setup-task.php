<?php
/**
 * Test the setup task functions.
 *
 * @package WC_Clearance
 */

use Automattic\WooCommerce\Admin\Features\OnboardingTasks\Task;
use WC_Clearance\Publish_Clearance_Page_Task;
use function WC_Clearance\create_clearance_page;
use function WC_Clearance\get_clearance_page_id;
use function WC_Clearance\mark_clearance_page_task_complete_hook;
use const WC_Clearance\CLEARANCE_PAGE_OPTION;

class Test_Setup_Task extends WP_UnitTestCase {

	public function test_get_id_returns_expected_id(): void {
		// Arrange.
		$task = new Publish_Clearance_Page_Task();

		// Act.
		$id = $task->get_id();

		// Assert.
		$this->assertSame( 'publish-clearance-page', $id );
	}

	public function test_get_title_returns_expected_title(): void {
		// Arrange.
		$task = new Publish_Clearance_Page_Task();

		// Act.
		$title = $task->get_title();

		// Assert.
		$this->assertSame( 'Publish the clearance section page', $title );
	}

	public function test_is_complete_returns_false_when_not_actioned(): void {
		// Arrange.
		delete_option( Task::ACTIONED_OPTION );
		$task = new Publish_Clearance_Page_Task();

		// Act.
		$result = $task->is_complete();

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_is_complete_returns_true_when_actioned(): void {
		// Arrange.
		$task = new Publish_Clearance_Page_Task();
		$task->mark_actioned();

		// Act.
		$result = $task->is_complete();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_mark_clearance_page_task_complete_hook_marks_task_complete_when_clearance_page_published(): void {
		// Arrange.
		delete_option( Task::ACTIONED_OPTION );
		$existing_id = (int) get_option( CLEARANCE_PAGE_OPTION );
		if ( $existing_id > 0 ) {
			wp_delete_post( $existing_id, true );
		}
		delete_option( CLEARANCE_PAGE_OPTION );
		create_clearance_page();
		$page_id = get_clearance_page_id();
		$post    = get_post( $page_id );

		// Act.
		mark_clearance_page_task_complete_hook( 'publish', 'draft', $post );

		// Assert.
		$task = new Publish_Clearance_Page_Task();
		$this->assertTrue( $task->is_complete() );
	}

	public function test_mark_clearance_page_task_complete_hook_does_nothing_when_different_page_published(): void {
		// Arrange.
		delete_option( Task::ACTIONED_OPTION );
		$other_post = self::factory()->post->create_and_get( array( 'post_type' => 'page' ) );

		// Act.
		mark_clearance_page_task_complete_hook( 'publish', 'draft', $other_post );

		// Assert.
		$task = new Publish_Clearance_Page_Task();
		$this->assertFalse( $task->is_complete() );
	}

	public function test_mark_clearance_page_task_complete_hook_does_nothing_when_status_is_not_publish(): void {
		// Arrange.
		delete_option( Task::ACTIONED_OPTION );
		$existing_id = (int) get_option( CLEARANCE_PAGE_OPTION );
		if ( $existing_id > 0 ) {
			wp_delete_post( $existing_id, true );
		}
		delete_option( CLEARANCE_PAGE_OPTION );
		create_clearance_page();
		$page_id = get_clearance_page_id();
		$post    = get_post( $page_id );

		// Act.
		mark_clearance_page_task_complete_hook( 'draft', 'auto-draft', $post );

		// Assert.
		$task = new Publish_Clearance_Page_Task();
		$this->assertFalse( $task->is_complete() );
	}
}
