<?php
/**
 * Tests for batch_discount_tiers() function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_discount_tier;
use function OutletPro\batch_discount_tiers;
use function OutletPro\get_discount_tiers;
use function OutletPro\get_price_tiers;
use function OutletPro\init_taxonomies;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Batch_Discount_Tiers extends WP_UnitTestCase {

	public function test_makes_batch_change(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		add_discount_tier( 50 );
		add_discount_tier( 70 );
		wp_insert_term( 'outlet-price-25', OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		batch_discount_tiers(
			array( 80, 90 ),
			array(
				50 => 60,
				80 => 85,
			),
			array( 70, 90 )
		);

		// Assert.
		$this->assertSame( array( 60, 85 ), get_discount_tiers() );
		$this->assertSame( array( 25 ), get_price_tiers() );
	}

	public function test_marks_search_index_dirty(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		add_discount_tier( 50 );
		add_discount_tier( 70 );
		delete_option( 'outletpro_search_index_dirty' );

		// Act.
		batch_discount_tiers( array( 80 ), array( 50 => 60 ), array( 70 ) );

		// Assert.
		$this->assertSame( 'yes', get_option( 'outletpro_search_index_dirty' ) );
	}

	public function test_rejects_non_integer_value(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		add_discount_tier( 70 );

		// Expect.
		$this->expectException( InvalidArgumentException::class );

		// Act.
		batch_discount_tiers(
			array( 'tier' => '90' ),
			array(),
			array( 'tier' => 70 )
		);
	}
}
