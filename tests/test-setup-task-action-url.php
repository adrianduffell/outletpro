<?php
/**
 * Test the setup_task_action_url function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\setup_task_action_url;

class Test_Setup_Task_Action_Url extends WP_UnitTestCase {

	public function test_returns_admin_products_list_table_url(): void {
		// Act.
		$url = setup_task_action_url();

		// Assert.
		$this->assertSame( admin_url( 'edit.php?post_type=product' ), $url );
	}
}
