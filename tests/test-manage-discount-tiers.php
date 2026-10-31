<?php
/**
 * Tests for the public discount tier mutation functions.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_discount_tier;
use function OutletPro\delete_discount_tier;
use function OutletPro\get_discount_tiers;
use function OutletPro\init_taxonomies;
use function OutletPro\set_search_index_facets;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use function OutletPro\update_discount_tier;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Manage_Discount_Tiers extends WP_UnitTestCase {

	public function test_update_preserves_assignments_and_delete_removes_them(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		add_discount_tier( 50 );
		add_discount_tier( 100 );
		$product = WC_Helper_Product::create_simple_product();
		set_search_index_facets( $product, 25, 50 );

		// Act.
		update_discount_tier( 50, 0 );

		// Assert.
		$this->assertSame( array( 0, 100 ), get_discount_tiers() );
		$this->assertSame( array( 'outlet-discount-0', 'outlet-price-25' ), wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) ) );

		// Act.
		delete_discount_tier( 0 );

		// Assert.
		$this->assertSame( array( 100 ), get_discount_tiers() );
		$this->assertSame( array( 'outlet-price-25' ), wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) ) );
	}

	public function test_add_rejects_duplicate_tier(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'Custom discount tier', OUTLET_SEARCH_INDEX_TAXONOMY, array( 'slug' => 'outlet-discount-50' ) );
		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Outlet discount tier could not be added.' );
		// Act.
		add_discount_tier( 50 );
	}

	public function test_add_rejects_negative_discount(): void {
		// Arrange.
		init_taxonomies();
		// Expect.
		$this->expectException( InvalidArgumentException::class );
		// Act.
		add_discount_tier( -50 );
	}

	public function test_add_rejects_discount_above_100(): void {
		// Arrange.
		init_taxonomies();
		// Expect.
		$this->expectException( InvalidArgumentException::class );
		// Act.
		add_discount_tier( 101 );
	}

	public function test_update_rejects_duplicate_tier(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		add_discount_tier( 30 );
		add_discount_tier( 50 );
		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Outlet discount tier could not be updated.' );
		// Act.
		update_discount_tier( 30, 50 );
	}

	public function test_update_rejects_replacement_above_100(): void {
		// Arrange.
		init_taxonomies();
		// Expect.
		$this->expectException( InvalidArgumentException::class );
		// Act.
		update_discount_tier( 50, 101 );
	}

	public function test_update_throws_when_tier_is_missing(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Outlet discount tier could not be found.' );
		// Act.
		update_discount_tier( 30, 50 );
	}

	public function test_delete_throws_when_tier_is_missing(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Outlet discount tier could not be found.' );
		// Act.
		delete_discount_tier( 50 );
	}
}
