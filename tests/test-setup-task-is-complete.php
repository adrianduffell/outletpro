<?php
/**
 * Test the setup_task_is_complete function.
 *
 * @package WC_Outlet
 */

use function WC_Outlet\add_to_outlet;
use function WC_Outlet\register_outlet_status_taxonomy;
use function WC_Outlet\seed_outlet_status_taxonomy;
use function WC_Outlet\setup_task_is_complete;

class Test_Setup_Task_Is_Complete extends WP_UnitTestCase {

	public function test_is_complete_returns_false_when_clearance_section_is_empty(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();

		// Act.
		$result = setup_task_is_complete();

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_is_complete_returns_true_when_clearance_section_has_products(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();

		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );

		// Act.
		$result = setup_task_is_complete();

		// Assert.
		$this->assertTrue( $result );
	}
}
