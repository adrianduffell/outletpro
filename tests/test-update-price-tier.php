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
use function OutletPro\truncate_outlet_search_index_taxonomy;
use function OutletPro\update_price_tier;

class Test_Update_Price_Tier extends WP_UnitTestCase {

	public function test_updates_tier(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		add_price_tier( 25 );
		add_price_tier( 100 );

		// Act.
		update_price_tier( 25, 0 );

		// Assert.
		$this->assertSame( array( 0, 100 ), get_price_tiers() );
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
