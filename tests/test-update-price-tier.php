<?php
/**
 * Tests for update_price_tier() function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_price_tier;
use function OutletPro\get_price_tiers;
use function OutletPro\init_taxonomies;
use function OutletPro\set_search_index_facets;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use function OutletPro\update_price_tier;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Update_Price_Tier extends WP_UnitTestCase {

	public function test_preserves_product_assignments(): void {
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
	}

	public function test_rejects_duplicate_tier(): void {
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

	public function test_rejects_negative_replacement(): void {
		// Arrange.
		init_taxonomies();

		// Expect.
		$this->expectException( InvalidArgumentException::class );

		// Act.
		update_price_tier( 25, -50 );
	}

	public function test_throws_when_tier_is_missing(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();

		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Outlet price tier could not be found.' );

		// Act.
		update_price_tier( 25, 50 );
	}
}
