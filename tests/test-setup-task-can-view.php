<?php
/**
 * Test the setup_task_can_view function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\setup_task_can_view;

class Test_Setup_Task_Can_View extends WP_UnitTestCase {

	public function test_returns_true_when_user_can_edit_products(): void {
		// Arrange.
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Act.
		$result = setup_task_can_view();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_returns_false_when_user_cannot_edit_products(): void {
		// Arrange.
		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		// Act.
		$result = setup_task_can_view();

		// Assert.
		$this->assertFalse( $result );
	}
}
