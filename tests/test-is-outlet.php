<?php
/**
 * Tests for is_outlet function
 *
 * @package OutletPro
 */

use function OutletPro\add_to_outlet;
use function OutletPro\is_outlet;
use function OutletPro\register_outlet_status_taxonomy;
use function OutletPro\remove_from_outlet;
use const OutletPro\OUTLET_STATUS_TAXONOMY;

class Test_Is_Outlet extends WP_UnitTestCase {

	public function test_throws_exception_when_taxonomy_does_not_exist(): void {
		// Arrange.
		unregister_taxonomy( OUTLET_STATUS_TAXONOMY );
		$product = WC_Helper_Product::create_simple_product();

		// Expect.
		$this->expectException( RuntimeException::class );

		// Act.
		is_outlet( $product );
	}

	public function test_returns_true_when_product_is_outlet(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );

		// Act.
		$result = is_outlet( $product );

		// Assert.
		$this->assertTrue( $result, 'Should return true when product is in outlet' );
	}

	public function test_returns_false_when_product_not_in_outlet(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();

		// Act.
		$result = is_outlet( $product );

		// Assert.
		$this->assertFalse( $result, 'Should return false when product is not in outlet' );
	}

	public function test_returns_false_after_removing_outlet_status(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		remove_from_outlet( $product );

		// Act.
		$result = is_outlet( $product );

		// Assert.
		$this->assertFalse( $result, 'Should return false after removing outlet status' );
	}

	public function test_variation_inherits_outlet_from_parent(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		$variable_product = \WC_Helper_Product::create_variation_product();
		add_to_outlet( $variable_product );
		$variation_id = $variable_product->get_children()[0];
		$variation    = wc_get_product( $variation_id );

		// Act.
		$result = is_outlet( $variation );

		// Assert.
		$this->assertTrue( $result, 'Variation should inherit outlet status from its parent' );
	}

	public function test_variation_not_outlet_when_parent_not_outlet(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		$variable_product = \WC_Helper_Product::create_variation_product();
		$variation_id     = $variable_product->get_children()[0];
		$variation        = wc_get_product( $variation_id );

		// Act.
		$result = is_outlet( $variation );

		// Assert.
		$this->assertFalse( $result, 'Variation should not be outlet when parent is not in outlet' );
	}
}
