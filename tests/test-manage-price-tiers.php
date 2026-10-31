<?php
/**
 * Tests for the public price tier mutation functions.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_price_tier;
use function OutletPro\deinit_taxonomies;
use function OutletPro\delete_price_tier;
use function OutletPro\get_price_tiers;
use function OutletPro\init_taxonomies;
use function OutletPro\set_search_index_facets;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use function OutletPro\update_price_tier;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Manage_Price_Tiers extends WP_UnitTestCase {

	public function test_update_preserves_assignments_and_delete_removes_them(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		add_price_tier( 25 );
		add_price_tier( 100 );
		$product = WC_Helper_Product::create_simple_product();
		set_search_index_facets( $product, 25, 50 );

		// Act.
		update_price_tier( 25, 0 );

		// Assert.
		$this->assertSame( array( 0, 100 ), get_price_tiers() );
		$this->assertSame( array( 'outlet-discount-50', 'outlet-price-0' ), wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) ) );

		// Act.
		delete_price_tier( 0 );

		// Assert.
		$this->assertSame( array( 100 ), get_price_tiers() );
		$this->assertSame( array( 'outlet-discount-50' ), wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) ) );
	}

	public function test_add_throws_when_taxonomy_is_missing(): void {
		// Arrange.
		deinit_taxonomies();
		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Outlet price tier could not be added.' );
		// Act.
		add_price_tier( 25 );
	}

	public function test_add_rejects_duplicate_tier(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'Custom price tier', OUTLET_SEARCH_INDEX_TAXONOMY, array( 'slug' => 'outlet-price-25' ) );
		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Outlet price tier could not be added.' );
		// Act.
		add_price_tier( 25 );
	}

	public function test_add_rejects_negative_price(): void {
		// Arrange.
		init_taxonomies();
		// Expect.
		$this->expectException( InvalidArgumentException::class );
		// Act.
		add_price_tier( -25 );
	}

	public function test_update_rejects_duplicate_tier(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		add_price_tier( 25 );
		add_price_tier( 50 );
		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Outlet price tier could not be updated.' );
		// Act.
		update_price_tier( 25, 50 );
	}

	public function test_update_rejects_negative_replacement(): void {
		// Arrange.
		init_taxonomies();
		// Expect.
		$this->expectException( InvalidArgumentException::class );
		// Act.
		update_price_tier( 25, -50 );
	}

	public function test_update_throws_when_tier_is_missing(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Outlet price tier could not be found.' );
		// Act.
		update_price_tier( 25, 50 );
	}

	public function test_delete_throws_when_tier_is_missing(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Outlet price tier could not be found.' );
		// Act.
		delete_price_tier( 25 );
	}
}
