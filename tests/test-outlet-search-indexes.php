<?php
/**
 * Tests for outlet search index taxonomy helpers.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\register_outlet_search_index_taxonomy;
use function OutletPro\seed_outlet_search_index_taxonomy;
use function OutletPro\set_search_index_facets;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Outlet_Search_Indexes extends WP_UnitTestCase {

	public function test_setting_search_indexes_replaces_existing_assignments(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		seed_outlet_search_index_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		wp_set_object_terms( $product->get_id(), 'outlet-price-100', OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		set_search_index_facets( $product, 25, 70 );

		// Assert.
		$this->assertSame( array( 'outlet-discount-70', 'outlet-price-25' ), wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) ) );
	}

	public function test_null_tiers_clear_assignments(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		wp_set_object_terms( $product->get_id(), array( 'outlet-price-25', 'outlet-discount-70' ), OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		set_search_index_facets( $product, null, null );

		// Assert.
		$this->assertSame( array(), wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) ) );
	}

	public function test_null_price_tier_removes_price_assignment(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		wp_set_object_terms( $product->get_id(), array( 'outlet-price-25', 'outlet-discount-70' ), OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		set_search_index_facets( $product, null, 70 );

		// Assert.
		$this->assertSame( array( 'outlet-discount-70' ), wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) ) );
	}

	public function test_null_discount_tier_removes_discount_assignment(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		wp_set_object_terms( $product->get_id(), array( 'outlet-price-25', 'outlet-discount-70' ), OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		set_search_index_facets( $product, 25, null );

		// Assert.
		$this->assertSame( array( 'outlet-price-25' ), wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) ) );
	}

	public function test_zero_tiers_are_assigned(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		$product = WC_Helper_Product::create_simple_product();

		// Act.
		set_search_index_facets( $product, 0, 0 );

		// Assert.
		$this->assertSame( array( 'outlet-discount-0', 'outlet-price-0' ), wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) ) );
	}

	public function test_setting_search_indexes_throws_when_taxonomy_is_missing(): void {
		// Arrange.
		$product = WC_Helper_Product::create_simple_product();
		unregister_taxonomy( OUTLET_SEARCH_INDEX_TAXONOMY );

		// Expect.
		$this->expectException( RuntimeException::class );

		// Act.
		set_search_index_facets( $product, 25, null );
	}
}
