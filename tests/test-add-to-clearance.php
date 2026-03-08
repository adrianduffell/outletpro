<?php
/**
 * Test the add_to_clearance function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_to_clearance;
use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\seed_clearance_status_taxonomy;
use const WC_Clearance\CLEARANCE_STATUS_CANONICAL_TERM;
use const WC_Clearance\CLEARANCE_STATUS_TAXONOMY;

class Test_Add_To_Clearance extends WP_UnitTestCase {

	public function test_throws_exception_when_taxonomy_not_registered(): void {
		// Arrange.
		if ( taxonomy_exists( CLEARANCE_STATUS_TAXONOMY ) ) {
			unregister_taxonomy( CLEARANCE_STATUS_TAXONOMY );
		}
		$product = \WC_Helper_Product::create_simple_product();

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		add_to_clearance( $product );
	}

	public function test_assigns_clearance_term_to_single_product(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();

		// Act.
		add_to_clearance( $product );

		// Assert.
		$terms = wp_get_object_terms( $product->get_id(), CLEARANCE_STATUS_TAXONOMY, array( 'fields' => 'names' ) );
		$this->assertContains( CLEARANCE_STATUS_CANONICAL_TERM, $terms );
	}

	public function test_throws_exception_on_insert_term_failure(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		foreach ( get_terms(
			array(
				'taxonomy'   => CLEARANCE_STATUS_TAXONOMY,
				'hide_empty' => false,
			)
		) as $term ) {
			wp_delete_term( $term->term_id, CLEARANCE_STATUS_TAXONOMY );
		}
		$product = \WC_Helper_Product::create_simple_product();

		add_filter(
			'pre_insert_term',
			fn() => new WP_Error( 'simulated_error' ),
		);

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		add_to_clearance( $product );
	}
}
