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

/**
 * Test class for add_to_clearance function.
 */
class Test_Add_To_Clearance extends WP_UnitTestCase {

	/**
	 * Test that a RuntimeException is thrown when the taxonomy is not registered.
	 */
	public function test_throws_runtimeexception_when_taxonomy_not_registered(): void {
		// Arrange.
		if ( taxonomy_exists( CLEARANCE_STATUS_TAXONOMY ) ) {
			unregister_taxonomy( CLEARANCE_STATUS_TAXONOMY );
		}

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		add_to_clearance();
	}

	/**
	 * Test that a single product is assigned the clearance term.
	 */
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

	/**
	 * Test that multiple products are each assigned the clearance term.
	 */
	public function test_assigns_clearance_term_to_multiple_products(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product_a = WC_Helper_Product::create_simple_product();
		$product_b = WC_Helper_Product::create_simple_product();

		// Act.
		add_to_clearance( $product_a, $product_b );

		// Assert.
		foreach ( array( $product_a, $product_b ) as $product ) {
			$terms = wp_get_object_terms( $product->get_id(), CLEARANCE_STATUS_TAXONOMY, array( 'fields' => 'names' ) );
			$this->assertContains( CLEARANCE_STATUS_CANONICAL_TERM, $terms );
		}
	}

	/**
	 * Test that a RuntimeException is thrown when wp_set_object_terms returns a WP_Error.
	 */
	public function test_throws_runtimeexception_when_wp_set_object_terms_returns_wp_error(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();

		add_filter(
			'wp_set_object_terms',
			static function( $tt_ids, $object_id, $terms, $taxonomy, $append, $old_tt_ids ) {
				return new \WP_Error( 'test_wp_error', 'Forced WP_Error for testing.' );
			},
			10,
			6
		);

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		try {
			add_to_clearance( $product );
		} finally {
			remove_filter( 'wp_set_object_terms', '__return_null', 10 );
			// Remove our anonymous filter by removing all filters for this hook at this priority.
			remove_all_filters( 'wp_set_object_terms', 10 );
		}
	}

	/**
	 * Test that a RuntimeException is thrown when wp_set_object_terms returns false.
	 */
	public function test_throws_runtimeexception_when_wp_set_object_terms_returns_false(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();

		add_filter(
			'wp_set_object_terms',
			static function( $tt_ids, $object_id, $terms, $taxonomy, $append, $old_tt_ids ) {
				return false;
			},
			10,
			6
		);

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		try {
			add_to_clearance( $product );
		} finally {
			remove_filter( 'wp_set_object_terms', '__return_null', 10 );
			// Remove our anonymous filter by removing all filters for this hook at this priority.
			remove_all_filters( 'wp_set_object_terms', 10 );
		}
	}
}
