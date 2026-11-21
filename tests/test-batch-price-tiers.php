<?php
/**
 * Tests for batch_price_tiers() function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_price_tier;
use function OutletPro\batch_price_tiers;
use function OutletPro\get_discount_tiers;
use function OutletPro\get_price_tiers;
use function OutletPro\init_taxonomies;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Batch_Price_Tiers extends WP_UnitTestCase {

	public function test_makes_batch_change(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		add_price_tier( 50 );
		add_price_tier( 100 );
		wp_insert_term( 'outlet-discount-30', OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		batch_price_tiers(
			array( 125, 150 ),
			array(
				50  => 60,
				125 => 130,
			),
			array( 100, 150 )
		);

		// Assert.
		$this->assertSame( array( 60, 130 ), get_price_tiers() );
		$this->assertSame( array( 30 ), get_discount_tiers() );
	}

	public function test_rejects_non_integer_value(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		add_price_tier( 100 );

		// Expect.
		$this->expectException( InvalidArgumentException::class );

		// Act.
		batch_price_tiers(
			array( 'tier' => '125' ),
			array(),
			array( 'tier' => 100 )
		);
	}
}
