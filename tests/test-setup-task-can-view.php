<?php
/**
 * Test the setup_task_can_view function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\create_clearance_page;
use function WC_Clearance\setup_task_can_view;
use const WC_Clearance\CLEARANCE_PAGE_OPTION;

class Test_Setup_Task_Can_View extends WP_UnitTestCase {

	public function test_returns_true_when_page_exists_and_user_can_edit_pages(): void {
		// Arrange.
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		delete_option( CLEARANCE_PAGE_OPTION );
		create_clearance_page();

		// Act.
		$result = setup_task_can_view();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_returns_false_when_no_clearance_page(): void {
		// Arrange.
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		delete_option( CLEARANCE_PAGE_OPTION );

		// Act.
		$result = setup_task_can_view();

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_returns_false_when_user_cannot_edit_pages(): void {
		// Arrange.
		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		delete_option( CLEARANCE_PAGE_OPTION );
		create_clearance_page();

		// Act.
		$result = setup_task_can_view();

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_returns_false_when_option_is_corrupted(): void {
		// Arrange.
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		update_option( CLEARANCE_PAGE_OPTION, 'not-an-int' );

		// Act.
		$result = setup_task_can_view();

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_returns_false_when_clearance_page_is_trashed(): void {
		// Arrange.
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		delete_option( CLEARANCE_PAGE_OPTION );
		create_clearance_page();
		$page_id = get_option( CLEARANCE_PAGE_OPTION );
		wp_trash_post( $page_id );

		// Act.
		$result = setup_task_can_view();

		// Assert.
		$this->assertFalse( $result );
	}
}
