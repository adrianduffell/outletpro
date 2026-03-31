<?php
/**
 * Test the setup_task_is_complete function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_to_clearance;
use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\seed_clearance_status_taxonomy;
use function WC_Clearance\setup_task_is_complete;

class Test_Setup_Task_Is_Complete extends WP_UnitTestCase {

	public function test_is_complete_returns_false_when_clearance_section_is_empty(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();

		// Act.
		$result = setup_task_is_complete();

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_is_complete_returns_true_when_clearance_section_has_products(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();

		$product = \WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );

		// Act.
		$result = setup_task_is_complete();

		// Assert.
		$this->assertTrue( $result );
	}
}
