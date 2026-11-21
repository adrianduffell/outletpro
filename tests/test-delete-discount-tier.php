<?php
/**
 * Tests for delete_discount_tier() function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_discount_tier;
use function OutletPro\delete_discount_tier;
use function OutletPro\get_discount_tiers;
use function OutletPro\init_taxonomies;
use function OutletPro\truncate_outlet_search_index_taxonomy;

class Test_Delete_Discount_Tier extends WP_UnitTestCase {

	public function test_removes_only_the_deleted_tier(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		add_discount_tier( 50 );
		add_discount_tier( 100 );

		// Act.
		delete_discount_tier( 50 );

		// Assert.
		$this->assertSame( array( 100 ), get_discount_tiers() );
	}

	public function test_throws_when_tier_is_missing(): void {
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
