<?php
/**
 * Tests for the classify_discount_tier function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\classify_discount_tier;
use function OutletPro\deinit_taxonomies;
use function OutletPro\init_taxonomies;
use function OutletPro\seed_outlet_search_index_taxonomy;
use function OutletPro\truncate_outlet_search_index_taxonomy;

class Test_Classify_Discount_Tier extends WP_UnitTestCase {

	public function test_classifies_variable_product_from_its_variation(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		seed_outlet_search_index_taxonomy();
		$product = new WC_Product_Variable();
		$product->save();
		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $product->get_id() );
		$variation->set_regular_price( '100' );
		$variation->set_sale_price( '40' );
		$variation->save();
		$product = wc_get_product( $product->get_id() );

		// Act.
		$tier = classify_discount_tier( $product );

		// Assert.
		$this->assertSame( 50, $tier );
	}

	public function test_finds_largest_matching_tier(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		seed_outlet_search_index_taxonomy();

		$product = new WC_Product();
		$product->set_regular_price( '100' );
		$product->set_price( '45' );

		// Act.
		$tier = classify_discount_tier( $product );

		// Assert.
		$this->assertSame( 50, $tier );
	}

	public function test_matches_exact_tier_boundary(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		seed_outlet_search_index_taxonomy();

		$product = new WC_Product();
		$product->set_regular_price( '100' );
		$product->set_price( '50' );

		// Act.
		$tier = classify_discount_tier( $product );

		// Assert.
		$this->assertSame( 50, $tier );
	}

	public function test_matches_discount_boundary_despite_floating_point_error(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		seed_outlet_search_index_taxonomy();
		$product = new WC_Product();
		$product->set_regular_price( '1.90' );
		$product->set_price( '1.33' );

		// Act.
		$tier = classify_discount_tier( $product );

		// Assert.
		$this->assertSame( 30, $tier );
	}

	public function test_rejects_discount_below_boundary_outside_tolerance(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		seed_outlet_search_index_taxonomy();
		$product = new WC_Product();
		$product->set_regular_price( '100' );
		$product->set_price( '70.00000001' );

		// Act.
		$tier = classify_discount_tier( $product );

		// Assert.
		$this->assertNull( $tier );
	}

	public function test_returns_null_when_no_tier_matches(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		seed_outlet_search_index_taxonomy();

		$product = new WC_Product();
		$product->set_regular_price( '100' );
		$product->set_price( '71' );

		// Act.
		$tier = classify_discount_tier( $product );

		// Assert.
		$this->assertNull( $tier );
	}

	public function test_returns_null_when_no_tiers_exist(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();

		$product = new WC_Product();
		$product->set_regular_price( '100' );
		$product->set_price( '75' );

		// Act.
		$tier = classify_discount_tier( $product );

		// Assert.
		$this->assertNull( $tier );
	}

	public function test_returns_null_when_product_has_no_discount(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		seed_outlet_search_index_taxonomy();
		$product = new WC_Product();
		$product->set_regular_price( '100' );
		$product->set_price( '100' );

		// Act.
		$tier = classify_discount_tier( $product );

		// Assert.
		$this->assertNull( $tier );
	}

	public function test_returns_null_when_regular_price_is_unset(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		seed_outlet_search_index_taxonomy();
		$product = new WC_Product();
		$product->set_price( '50' );

		// Act.
		$tier = classify_discount_tier( $product );

		// Assert.
		$this->assertNull( $tier );
	}

	public function test_returns_null_when_product_price_is_unset(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		seed_outlet_search_index_taxonomy();
		$product = new WC_Product();
		$product->set_regular_price( '100' );

		// Act.
		$tier = classify_discount_tier( $product );

		// Assert.
		$this->assertNull( $tier );
	}

	public function test_zero_product_price_matches_highest_discount_tier(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		seed_outlet_search_index_taxonomy();
		$product = new WC_Product();
		$product->set_regular_price( '100' );
		$product->set_price( '0' );

		// Act.
		$tier = classify_discount_tier( $product );

		// Assert.
		$this->assertSame( 70, $tier );
	}

	public function test_throws_when_price_is_malformed(): void {
		// Arrange.
		$product = new WC_Product();
		$product->set_regular_price( '100' );
		$product->set_price( '50' );
		add_filter( 'woocommerce_product_get_price', '__return_empty_array', PHP_INT_MAX );

		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Outlet product price is invalid.' );

		// Act.
		classify_discount_tier( $product );
	}

	public function test_throws_when_taxonomy_is_missing(): void {
		// Arrange.
		deinit_taxonomies();

		$product = new WC_Product();
		$product->set_regular_price( '100' );
		$product->set_price( '50' );

		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Outlet discount tiers could not be retrieved.' );

		// Act.
		classify_discount_tier( $product );
	}
}
