<?php
/**
 * Test the count_clearance function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_to_clearance;
use function WC_Clearance\count_clearance;
use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\seed_clearance_status_taxonomy;
use const WC_Clearance\CLEARANCE_STATUS_TAXONOMY;

class Test_Count_Clearance extends WP_UnitTestCase {

	public function test_throws_exception_when_taxonomy_not_registered(): void {
		// Arrange.
		unregister_taxonomy( CLEARANCE_STATUS_TAXONOMY );

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		count_clearance();
	}

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

		$product_one = \WC_Helper_Product::create_simple_product();
		$product_two = \WC_Helper_Product::create_simple_product();
		add_to_clearance( $product_one );
		add_to_clearance( $product_two );

		// Act.
		$count = count_clearance();

		// Assert.
		$this->assertSame( 2, $count );
	}

	public function test_only_counts_published_products(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();

		$published_product = \WC_Helper_Product::create_simple_product();
		$draft_product     = \WC_Helper_Product::create_simple_product();
		wp_update_post(
			array(
				'ID'          => $draft_product->get_id(),
				'post_status' => 'draft',
			)
		);
		add_to_clearance( $published_product );
		add_to_clearance( $draft_product );

		// Act.
		$count = count_clearance();

		// Assert.
		$this->assertSame( 1, $count );
	}
}
