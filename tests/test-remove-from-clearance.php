<?php
/**
 * Test the remove_from_clearance function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\remove_from_clearance;
use function WC_Clearance\seed_clearance_status_taxonomy;
use const WC_Clearance\CLEARANCE_STATUS_CANONICAL_TERM;
use const WC_Clearance\CLEARANCE_STATUS_TAXONOMY;

class Test_Remove_From_Clearance extends WP_UnitTestCase {

	public function test_removes_product_from_clearance(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		wp_set_object_terms( $product->get_id(), CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

		// Act.
		remove_from_clearance( $product );

		// Assert.
		$terms = wp_get_object_terms( $product->get_id(), CLEARANCE_STATUS_TAXONOMY );
		$this->assertEmpty( $terms );
	}

	public function test_removes_multiple_products_from_clearance(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product_one = \WC_Helper_Product::create_simple_product();
		$product_two = \WC_Helper_Product::create_simple_product();
		wp_set_object_terms( $product_one->get_id(), CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );
		wp_set_object_terms( $product_two->get_id(), CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

		// Act.
		remove_from_clearance( $product_one, $product_two );

		// Assert.
		$terms_one = wp_get_object_terms( $product_one->get_id(), CLEARANCE_STATUS_TAXONOMY );
		$terms_two = wp_get_object_terms( $product_two->get_id(), CLEARANCE_STATUS_TAXONOMY );
		$this->assertEmpty( $terms_one );
		$this->assertEmpty( $terms_two );
	}

	public function test_does_not_error_when_product_not_in_clearance(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();

		// Act & Assert (no exception should be thrown).
		remove_from_clearance( $product );
		$terms = wp_get_object_terms( $product->get_id(), CLEARANCE_STATUS_TAXONOMY );
		$this->assertEmpty( $terms );
	}

	public function test_throws_runtimeexception_when_taxonomy_does_not_exist(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		unregister_taxonomy( CLEARANCE_STATUS_TAXONOMY );

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		remove_from_clearance( $product );
	}
}
