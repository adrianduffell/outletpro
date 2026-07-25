<?php
/**
 * Test the count_outlet function.
 *
 * @package OutletPro
 */

use function OutletPro\add_to_outlet;
use function OutletPro\count_outlet;
use function OutletPro\register_outlet_status_taxonomy;
use function OutletPro\seed_outlet_status_taxonomy;
use const OutletPro\OUTLET_STATUS_TAXONOMY;

class Test_Count_Outlet extends WP_UnitTestCase {

	public function test_throws_exception_when_taxonomy_not_registered(): void {
		// Arrange.
		unregister_taxonomy( OUTLET_STATUS_TAXONOMY );

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		count_outlet();
	}

	public function test_returns_zero_when_canonical_term_does_not_exist(): void {
		// Arrange.
		register_outlet_status_taxonomy();

		// Act.
		$count = count_outlet();

		// Assert.
		$this->assertSame( 0, $count );
	}

	public function test_returns_zero_when_no_products_in_outlet(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();

		// Act.
		$count = count_outlet();

		// Assert.
		$this->assertSame( 0, $count );
	}

	public function test_returns_correct_count_of_outlet_products(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();

		$product_one = \WC_Helper_Product::create_simple_product();
		$product_two = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product_one );
		add_to_outlet( $product_two );

		// Act.
		$count = count_outlet();

		// Assert.
		$this->assertSame( 2, $count );
	}

	public function test_only_counts_published_products(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();

		$published_product = \WC_Helper_Product::create_simple_product();
		$draft_product     = \WC_Helper_Product::create_simple_product();
		wp_update_post(
			array(
				'ID'          => $draft_product->get_id(),
				'post_status' => 'draft',
			)
		);
		add_to_outlet( $published_product );
		add_to_outlet( $draft_product );

		// Act.
		$count = count_outlet();

		// Assert.
		$this->assertSame( 1, $count );
	}
}
