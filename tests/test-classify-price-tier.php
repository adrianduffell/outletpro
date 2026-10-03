<?php
/**
 * Tests for the classify_price_tier function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\classify_price_tier;
use function OutletPro\deinit_taxonomies;
use function OutletPro\init_taxonomies;
use function OutletPro\seed_outlet_search_index_taxonomy;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Classify_Price_Tier extends WP_UnitTestCase {

	public function test_classifies_variable_product_from_its_variation(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		seed_outlet_search_index_taxonomy();
		$product = new WC_Product_Variable();
		$product->save();
		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $product->get_id() );
		$variation->set_regular_price( '20' );
		$variation->save();
		$product = wc_get_product( $product->get_id() );

		// Act.
		$tier = classify_price_tier( $product );

		// Assert.
		$this->assertSame( 25, $tier );
	}

	public function test_finds_smallest_matching_tier(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		seed_outlet_search_index_taxonomy();

		$product = new WC_Product();
		$product->set_price( '20' );

		// Act.
		$tier = classify_price_tier( $product );

		// Assert.
		$this->assertSame( 25, $tier );
	}

	public function test_matches_exact_tier_boundary(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		seed_outlet_search_index_taxonomy();

		$product = new WC_Product();
		$product->set_price( '25' );

		// Act.
		$tier = classify_price_tier( $product );

		// Assert.
		$this->assertSame( 25, $tier );
	}

	public function test_returns_null_when_no_tier_matches(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		seed_outlet_search_index_taxonomy();

		$product = new WC_Product();
		$product->set_price( '101' );

		// Act.
		$tier = classify_price_tier( $product );

		// Assert.
		$this->assertNull( $tier );
	}

	public function test_returns_null_when_no_tiers_exist(): void {
		// Arrange.
		init_taxonomies();
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
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		seed_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-price-invalid', OUTLET_SEARCH_INDEX_TAXONOMY );

		$product = new WC_Product();
		$product->set_price( '25' );

		// Act.
		$tier = classify_price_tier( $product );

		// Assert.
		$this->assertSame( 25, $tier );
	}

	public function test_skips_non_integer_tier_threshold(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		seed_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-price-15-dollars', OUTLET_SEARCH_INDEX_TAXONOMY );

		$product = new WC_Product();
		$product->set_price( '15' );

		// Act.
		$tier = classify_price_tier( $product );

		// Assert.
		$this->assertSame( 25, $tier );
	}

	public function test_classifies_fractional_product_price(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		seed_outlet_search_index_taxonomy();
		$product = new WC_Product();
		$product->set_price( '25.01' );

		// Act.
		$tier = classify_price_tier( $product );

		// Assert.
		$this->assertSame( 50, $tier );
	}

	public function test_returns_null_when_product_price_is_unset(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		seed_outlet_search_index_taxonomy();
		$product = new WC_Product();

		// Act.
		$tier = classify_price_tier( $product );

		// Assert.
		$this->assertNull( $tier );
	}

	public function test_zero_product_price_matches_lowest_tier(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		seed_outlet_search_index_taxonomy();
		$product = new WC_Product();
		$product->set_price( '0' );

		// Act.
		$tier = classify_price_tier( $product );

		// Assert.
		$this->assertSame( 10, $tier );
	}

	public function test_throws_when_taxonomy_is_missing(): void {
		// Arrange.
		deinit_taxonomies();

		$product = new WC_Product();
		$product->set_price( '25' );

		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Outlet price tiers could not be retrieved.' );

		// Act.
		classify_price_tier( $product );
	}
}
