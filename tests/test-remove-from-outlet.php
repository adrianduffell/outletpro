<?php
/**
 * Test the remove_from_outlet function.
 *
 * @package WC_Outlet
 */

use function WC_Outlet\register_outlet_status_taxonomy;
use function WC_Outlet\remove_from_outlet;
use function WC_Outlet\seed_outlet_status_taxonomy;
use const WC_Outlet\OUTLET_STATUS_CANONICAL_TERM;
use const WC_Outlet\OUTLET_STATUS_TAXONOMY;

class Test_Remove_From_Outlet extends WP_UnitTestCase {

	public function test_removes_product_from_outlet(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		wp_set_object_terms( $product->get_id(), OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY );

		// Act.
		remove_from_outlet( $product );

		// Assert.
		$terms = wp_get_object_terms( $product->get_id(), OUTLET_STATUS_TAXONOMY );
		$this->assertEmpty( $terms );
	}

	public function test_does_not_error_when_product_not_in_outlet(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();

		// Act & Assert (no exception should be thrown).
		remove_from_outlet( $product );
		$terms = wp_get_object_terms( $product->get_id(), OUTLET_STATUS_TAXONOMY );
		$this->assertEmpty( $terms );
	}

	public function test_throws_runtimeexception_when_taxonomy_does_not_exist(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		unregister_taxonomy( OUTLET_STATUS_TAXONOMY );

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		remove_from_outlet( $product );
	}
}
