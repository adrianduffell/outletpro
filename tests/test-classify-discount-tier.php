<?php
/**
 * Tests for the classify_discount_tier function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\classify_discount_tier;
use function OutletPro\register_outlet_search_index_taxonomy;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Classify_Discount_Tier extends WP_UnitTestCase {

	public function test_classifies_variable_product_from_its_variation(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-discount-30', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-discount-50', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-discount-70', OUTLET_SEARCH_INDEX_TAXONOMY );
		$product = new WC_Product_Variable();
		$product->save();
		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $product->get_id() );
		$variation->set_regular_price( '100' );
		$variation->set_sale_price( '40' );
		$variation->save();
		$product->set_children( array( $variation->get_id() ) );

		// Act.
		$tier = classify_discount_tier( $product );

		// Assert.
		$this->assertSame( 50, $tier );
	}

	public function test_finds_largest_matching_tier(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-discount-30', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-price-25', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-discount-70', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-discount-50', OUTLET_SEARCH_INDEX_TAXONOMY );

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
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-discount-30', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-price-25', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-discount-70', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-discount-50', OUTLET_SEARCH_INDEX_TAXONOMY );

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
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-discount-30', OUTLET_SEARCH_INDEX_TAXONOMY );
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
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-discount-30', OUTLET_SEARCH_INDEX_TAXONOMY );
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
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-discount-30', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-price-25', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-discount-70', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-discount-50', OUTLET_SEARCH_INDEX_TAXONOMY );

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
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();

		$product = new WC_Product();
		$product->set_regular_price( '100' );
		$product->set_price( '75' );

		// Act.
		$tier = classify_discount_tier( $product );

		// Assert.
		$this->assertNull( $tier );
	}

	public function test_skips_invalid_tier_threshold(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-discount-invalid', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-discount-30', OUTLET_SEARCH_INDEX_TAXONOMY );

		$product = new WC_Product();
		$product->set_regular_price( '100' );
		$product->set_price( '50' );

		// Act.
		$tier = classify_discount_tier( $product );

		// Assert.
		$this->assertSame( 30, $tier );
	}

	public function test_skips_non_integer_tier_threshold(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-discount-50-percent', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-discount-50', OUTLET_SEARCH_INDEX_TAXONOMY );

		$product = new WC_Product();
		$product->set_regular_price( '100' );
		$product->set_price( '45' );

		// Act.
		$tier = classify_discount_tier( $product );

		// Assert.
		$this->assertSame( 50, $tier );
	}

	public function test_returns_null_when_all_thresholds_are_invalid(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-discount-invalid', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-discount-50-percent', OUTLET_SEARCH_INDEX_TAXONOMY );

		$product = new WC_Product();
		$product->set_regular_price( '100' );
		$product->set_price( '45' );

		// Act.
		$tier = classify_discount_tier( $product );

		// Assert.
		$this->assertNull( $tier );
	}

	public function test_returns_null_when_product_has_no_discount(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-discount-30', OUTLET_SEARCH_INDEX_TAXONOMY );
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
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-discount-30', OUTLET_SEARCH_INDEX_TAXONOMY );
		$product = new WC_Product();
		$product->set_price( '50' );

		// Act.
		$tier = classify_discount_tier( $product );

		// Assert.
		$this->assertNull( $tier );
	}

	public function test_throws_when_taxonomy_is_missing(): void {
		// Arrange.
		unregister_taxonomy( OUTLET_SEARCH_INDEX_TAXONOMY );

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
