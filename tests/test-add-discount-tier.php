<?php
/**
 * Tests for add_discount_tier() function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_discount_tier;
use function OutletPro\get_discount_tiers;
use function OutletPro\init_taxonomies;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Add_Discount_Tier extends WP_UnitTestCase {

	public function test_adds_discount_tiers_at_inclusive_bounds(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();

		// Act.
		add_discount_tier( 0 );
		add_discount_tier( 100 );

		// Assert.
		$this->assertSame( array( 0, 100 ), get_discount_tiers() );
	}

	public function test_rejects_duplicate_tier(): void {
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

	public function test_rejects_negative_discount(): void {
		// Arrange.
		init_taxonomies();

		// Expect.
		$this->expectException( InvalidArgumentException::class );

		// Act.
		add_discount_tier( -50 );
	}

	public function test_rejects_discount_above_100(): void {
		// Arrange.
		init_taxonomies();

		// Expect.
		$this->expectException( InvalidArgumentException::class );

		// Act.
		add_discount_tier( 101 );
	}
}
