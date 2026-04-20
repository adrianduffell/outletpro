<?php
/**
 * Tests for is_clearance function
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_to_clearance;
use function WC_Clearance\is_clearance;
use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\remove_from_clearance;
use const WC_Clearance\CLEARANCE_STATUS_TAXONOMY;

class Test_Is_Clearance extends WP_UnitTestCase {

	public function test_throws_exception_when_taxonomy_does_not_exist(): void {
		// Arrange.
		unregister_taxonomy( CLEARANCE_STATUS_TAXONOMY );
		$product = WC_Helper_Product::create_simple_product();

		// Expect.
		$this->expectException( RuntimeException::class );

		// Act.
		is_clearance( $product );
	}

	public function test_returns_true_when_product_is_clearance(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );

		// Act.
		$result = is_clearance( $product );

		// Assert.
		$this->assertTrue( $result, 'Should return true when product is on clearance' );
	}

	public function test_returns_false_when_product_not_on_clearance(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();

		// Act.
		$result = is_clearance( $product );

		// Assert.
		$this->assertFalse( $result, 'Should return false when product is not on clearance' );
	}

	public function test_returns_false_after_removing_clearance_status(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		remove_from_clearance( $product );

		// Act.
		$result = is_clearance( $product );

		// Assert.
		$this->assertFalse( $result, 'Should return false after removing clearance status' );
	}

	public function test_throws_exception_when_variation_parent_not_found(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$variable_product = \WC_Helper_Product::create_variation_product();
		$variation_id     = $variable_product->get_children()[0];
		$variation        = wc_get_product( $variation_id );
		$variable_product->delete( true );

		// Expect.
		$this->expectException( RuntimeException::class );

		// Act.
		is_clearance( $variation );
	}

	public function test_variation_inherits_clearance_from_parent(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$variable_product = \WC_Helper_Product::create_variation_product();
		add_to_clearance( $variable_product );
		$variation_id = $variable_product->get_children()[0];
		$variation    = wc_get_product( $variation_id );

		// Act.
		$result = is_clearance( $variation );

		// Assert.
		$this->assertTrue( $result, 'Variation should inherit clearance status from its parent' );
	}

	public function test_variation_not_clearance_when_parent_not_clearance(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$variable_product = \WC_Helper_Product::create_variation_product();
		$variation_id     = $variable_product->get_children()[0];
		$variation        = wc_get_product( $variation_id );

		// Act.
		$result = is_clearance( $variation );

		// Assert.
		$this->assertFalse( $result, 'Variation should not be clearance when parent is not on clearance' );
	}
}
