<?php
/**
 * Test setup task title formatting.
 *
 * @package OutletPro
 */

class Test_Setup_Task_Title extends WP_UnitTestCase {

	public function test_setup_task_title_has_no_leading_whitespace(): void {
		// Arrange.
		$setup_task_source = file_get_contents( dirname( __DIR__ ) . '/includes/setup-task.php' );

		// Act.
		$has_leading_whitespace_in_title = false !== strpos( $setup_task_source, "__( ' Choose outlet products', 'outletpro' )" );

		// Assert.
		$this->assertFalse( $has_leading_whitespace_in_title );
	}
}
