<?php
/**
 * Tests for add_price_tier() function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_price_tier;
use function OutletPro\deinit_taxonomies;
use function OutletPro\get_price_tiers;
use function OutletPro\init_taxonomies;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Add_Price_Tier extends WP_UnitTestCase {

	public function test_adds_price_tiers_including_zero(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();

		// Act.
		add_price_tier( 0 );
		add_price_tier( 100 );

		// Assert.
		$this->assertSame( array( 0, 100 ), get_price_tiers() );
	}

	public function test_throws_when_taxonomy_is_missing(): void {
		// Arrange.
		deinit_taxonomies();

		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Outlet price tier could not be added.' );

		// Act.
		add_price_tier( 25 );
	}

	public function test_rejects_duplicate_tier(): void {
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

	public function test_rejects_negative_price(): void {
		// Arrange.
		init_taxonomies();

		// Expect.
		$this->expectException( InvalidArgumentException::class );

		// Act.
		add_price_tier( -25 );
	}
}
