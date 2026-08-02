<?php
/**
 * Test the setup_task_is_complete function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\register_outlet_status_taxonomy;
use function OutletPro\seed_outlet_status_taxonomy;
use function OutletPro\setup_task_is_complete;

class Test_Setup_Task_Is_Complete extends WP_UnitTestCase {

	public function test_is_complete_returns_false_when_outlet_section_is_empty(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();

		// Act.
		$result = setup_task_is_complete();

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_is_complete_returns_true_when_outlet_section_has_products(): void {
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
