<?php
/**
 * Test the add_to_outlet function.
 *
 * @package WC_Outlet
 */

use function WC_Outlet\add_to_outlet;
use function WC_Outlet\register_outlet_status_taxonomy;
use function WC_Outlet\seed_outlet_status_taxonomy;
use const WC_Outlet\OUTLET_STATUS_CANONICAL_TERM;
use const WC_Outlet\OUTLET_STATUS_TAXONOMY;

class Test_Add_To_Outlet extends WP_UnitTestCase {

	public function test_throws_exception_when_taxonomy_not_registered(): void {
		// Arrange.
		unregister_taxonomy( OUTLET_STATUS_TAXONOMY );
		$product = \WC_Helper_Product::create_simple_product();

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		add_to_outlet( $product );
	}

	public function test_assigns_clearance_term_to_single_product(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();

		// Act.
		add_to_outlet( $product );

		// Assert.
		$terms = wp_get_object_terms( $product->get_id(), OUTLET_STATUS_TAXONOMY, array( 'fields' => 'names' ) );
		$this->assertContains( OUTLET_STATUS_CANONICAL_TERM, $terms );
	}

	public function test_throws_exception_on_insert_term_failure(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		foreach ( get_terms(
			array(
				'taxonomy'   => OUTLET_STATUS_TAXONOMY,
				'hide_empty' => false,
			)
		) as $term ) {
			wp_delete_term( $term->term_id, OUTLET_STATUS_TAXONOMY );
		}
		$product = \WC_Helper_Product::create_simple_product();

		add_filter(
			'pre_insert_term',
			fn() => new WP_Error( 'simulated_error' ),
		);

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		add_to_outlet( $product );
	}
}
