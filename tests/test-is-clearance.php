<?php
/**
 * Tests for is_clearance function
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_to_clearance;
use function WC_Clearance\is_clearance;
use function WC_Clearance\register_taxonomy_for_clearance_status;
use function WC_Clearance\remove_from_clearance;

class Test_Is_Clearance extends WP_UnitTestCase {

	public function test_throws_exception_when_taxonomy_does_not_exist(): void {
		// Arrange.
		unregister_taxonomy( 'wc_clearance_status' );
		$product = WC_Helper_Product::create_simple_product();

		// Expect.
		$this->expectException( RuntimeException::class );

		// Act.
		is_clearance( $product );
	}

	public function test_returns_true_when_product_is_clearance(): void {
		// Arrange.
		register_taxonomy_for_clearance_status();
		$product = WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );

		// Act.
		$result = is_clearance( $product );

		// Assert.
		$this->assertTrue( $result, 'Should return true when product is on clearance' );
	}

	public function test_returns_false_when_product_not_on_clearance(): void {
		// Arrange.
		register_taxonomy_for_clearance_status();
		$product = WC_Helper_Product::create_simple_product();

		// Act.
		$result = is_clearance( $product );

		// Assert.
		$this->assertFalse( $result, 'Should return false when product is not on clearance' );
	}

	public function test_returns_false_after_removing_clearance_status(): void {
		// Arrange.
		register_taxonomy_for_clearance_status();
		$product = WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		remove_from_clearance( $product );

		// Act.
		$result = is_clearance( $product );

		// Assert.
		$this->assertFalse( $result, 'Should return false after removing clearance status' );
	}

	public function test_handles_multiple_products(): void {
		// Arrange.
		register_taxonomy_for_clearance_status();
		$product1 = WC_Helper_Product::create_simple_product();
		$product2 = WC_Helper_Product::create_simple_product();
		$product3 = WC_Helper_Product::create_simple_product();

		// Act.
		add_to_clearance( $product1, $product3 );

		// Assert.
		$this->assertTrue( is_clearance( $product1 ), 'Product 1 should be on clearance' );
		$this->assertFalse( is_clearance( $product2 ), 'Product 2 should not be on clearance' );
		$this->assertTrue( is_clearance( $product3 ), 'Product 3 should be on clearance' );
	}

	public function test_works_with_different_product_types(): void {
		// Arrange.

		$variable_product = WC_Helper_Product::create_variation_product();
		add_to_clearance( $variable_product );

		// Act.
		$result = is_clearance( $variable_product );

		// Assert.
		$this->assertTrue( $result, 'Should work with variable products' );
	}
}
