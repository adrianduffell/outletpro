<?php
/**
 * Tests for the classify_price_tier function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\classify_price_tier;
use function OutletPro\register_outlet_search_index_taxonomy;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Classify_Price_Tier extends WP_UnitTestCase {

	public function test_classifies_variable_product_from_its_variation(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-price-10', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-price-25', OUTLET_SEARCH_INDEX_TAXONOMY );
		$product = new WC_Product_Variable();
		$product->save();
		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $product->get_id() );
		$variation->set_regular_price( '20' );
		$variation->save();
		$product->set_children( array( $variation->get_id() ) );

		// Act.
		$tier = classify_price_tier( $product );

		// Assert.
		$this->assertSame( 25, $tier );
	}

	public function test_finds_smallest_matching_tier(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-price-100', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-discount-70', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-price-50', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-price-25', OUTLET_SEARCH_INDEX_TAXONOMY );

		$product = new WC_Product();
		$product->set_price( '40' );

		// Act.
		$tier = classify_price_tier( $product );

		// Assert.
		$this->assertSame( 50, $tier );
	}

	public function test_matches_exact_tier_boundary(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-price-100', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-discount-70', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-price-50', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-price-25', OUTLET_SEARCH_INDEX_TAXONOMY );

		$product = new WC_Product();
		$product->set_price( '25' );

		// Act.
		$tier = classify_price_tier( $product );

		// Assert.
		$this->assertSame( 25, $tier );
	}

	public function test_returns_null_when_no_tier_matches(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-price-100', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-discount-70', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-price-50', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-price-25', OUTLET_SEARCH_INDEX_TAXONOMY );

		$product = new WC_Product();
		$product->set_price( '101' );

		// Act.
		$tier = classify_price_tier( $product );

		// Assert.
		$this->assertNull( $tier );
	}

	public function test_returns_null_when_no_tiers_exist(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();

		$product = new WC_Product();
		$product->set_price( '25' );

		// Act.
		$tier = classify_price_tier( $product );

		// Assert.
		$this->assertNull( $tier );
	}

	public function test_skips_invalid_tier_threshold(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-price-invalid', OUTLET_SEARCH_INDEX_TAXONOMY );

		wp_insert_term( 'outlet-price-50', OUTLET_SEARCH_INDEX_TAXONOMY );

		$product = new WC_Product();
		$product->set_price( '25' );

		// Act.
		$tier = classify_price_tier( $product );

		// Assert.
		$this->assertSame( 50, $tier );
	}

	public function test_skips_non_integer_tier_threshold(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-price-25-dollars', OUTLET_SEARCH_INDEX_TAXONOMY );

		wp_insert_term( 'outlet-price-50', OUTLET_SEARCH_INDEX_TAXONOMY );

		$product = new WC_Product();
		$product->set_price( '25' );

		// Act.
		$tier = classify_price_tier( $product );

		// Assert.
		$this->assertSame( 50, $tier );
	}

	public function test_classifies_fractional_product_price(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-price-25', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-price-50', OUTLET_SEARCH_INDEX_TAXONOMY );
		$product = new WC_Product();
		$product->set_price( '25.01' );

		// Act.
		$tier = classify_price_tier( $product );

		// Assert.
		$this->assertSame( 50, $tier );
	}

	public function test_unset_product_price_matches_lowest_tier(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-price-10', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-price-25', OUTLET_SEARCH_INDEX_TAXONOMY );
		$product = new WC_Product();

		// Act.
		$tier = classify_price_tier( $product );

		// Assert.
		$this->assertSame( 10, $tier );
	}

	public function test_throws_when_taxonomy_is_missing(): void {
		// Arrange.
		unregister_taxonomy( OUTLET_SEARCH_INDEX_TAXONOMY );

		$product = new WC_Product();
		$product->set_price( '25' );

		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Outlet price tiers could not be retrieved.' );

		// Act.
		classify_price_tier( $product );
	}
}
