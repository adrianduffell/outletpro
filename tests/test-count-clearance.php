<?php
/**
 * Test the count_clearance function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\count_clearance;
use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\seed_clearance_status_taxonomy;
use const WC_Clearance\CLEARANCE_STATUS_CANONICAL_TERM;
use const WC_Clearance\CLEARANCE_STATUS_TAXONOMY;

class Test_Count_Clearance extends WP_UnitTestCase {

	public function test_returns_zero_when_canonical_term_does_not_exist(): void {
		// Arrange.
		register_clearance_status_taxonomy();

		// Act.
		$count = count_clearance();

		// Assert.
		$this->assertSame( 0, $count );
	}

	public function test_returns_zero_when_no_products_in_clearance(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();

		// Act.
		$count = count_clearance();

		// Assert.
		$this->assertSame( 0, $count );
	}

	public function test_returns_correct_count_of_clearance_products(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$term = get_term_by( 'name', CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

		$product_one = \WC_Helper_Product::create_simple_product();
		$product_two = \WC_Helper_Product::create_simple_product();
		wp_set_object_terms( $product_one->get_id(), $term->term_id, CLEARANCE_STATUS_TAXONOMY );
		wp_set_object_terms( $product_two->get_id(), $term->term_id, CLEARANCE_STATUS_TAXONOMY );

		// Act.
		$count = count_clearance();

		// Assert.
		$this->assertSame( 2, $count );
	}

	public function test_only_counts_published_products(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$term = get_term_by( 'name', CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

		$published_product = \WC_Helper_Product::create_simple_product();
		$draft_product     = \WC_Helper_Product::create_simple_product();
		wp_update_post(
			array(
				'ID'          => $draft_product->get_id(),
				'post_status' => 'draft',
			)
		);
		wp_set_object_terms( $published_product->get_id(), $term->term_id, CLEARANCE_STATUS_TAXONOMY );
		wp_set_object_terms( $draft_product->get_id(), $term->term_id, CLEARANCE_STATUS_TAXONOMY );

		// Act.
		$count = count_clearance();

		// Assert.
		$this->assertSame( 1, $count );
	}
}
