<?php
/**
 * Tests for batch_search_facets() function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_discount_tier;
use function OutletPro\add_price_tier;
use function OutletPro\add_to_outlet;
use function OutletPro\batch_search_facets;
use function OutletPro\get_discount_tiers;
use function OutletPro\get_price_tiers;
use function OutletPro\init_products;
use function OutletPro\init_taxonomies;
use function OutletPro\seed_outlet_status_taxonomy;
use function OutletPro\truncate_outlet_search_index_taxonomy;

class Test_Batch_Search_Facets extends WP_UnitTestCase {

	public function test_makes_batch_change(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		add_price_tier( 50 );
		add_price_tier( 100 );
		add_discount_tier( 50 );
		add_discount_tier( 70 );

		// Act.
		batch_search_facets(
			array( 125, 150 ),
			array(
				50  => 60,
				125 => 130,
			),
			array( 100, 150 ),
			array( 80, 90 ),
			array(
				50 => 60,
				80 => 85,
			),
			array( 70, 90 )
		);

		// Assert.
		$this->assertSame( array( 60, 130 ), get_price_tiers() );
		$this->assertSame( array( 60, 85 ), get_discount_tiers() );
	}

	public function test_reindexes_once(): void {
		// Arrange.
		init_taxonomies();
		init_products();
		seed_outlet_status_taxonomy();
		truncate_outlet_search_index_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		as_unschedule_all_actions( 'outletpro_reindex_search_facets_batch', null, 'outletpro' );

		// Act.
		batch_search_facets( array( 125 ), array(), array(), array( 80 ) );

		// Assert.
		$this->assertCount(
			1,
			as_get_scheduled_actions(
				array(
					'hook'   => 'outletpro_reindex_search_facets_batch',
					'args'   => array( array( $product->get_id() ) ),
					'group'  => 'outletpro',
					'status' => ActionScheduler_Store::STATUS_PENDING,
				),
				'ids'
			)
		);
	}

	public function test_rejects_non_integer_price(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		add_discount_tier( 70 );

		// Expect.
		$this->expectException( InvalidArgumentException::class );

		// Act.
		batch_search_facets(
			array( 'tier' => '125' ),
			array(),
			array(),
			array(),
			array(),
			array( 'tier' => 70 )
		);
	}

	public function test_rejects_non_integer_discount(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();

		// Expect.
		$this->expectException( InvalidArgumentException::class );

		// Act.
		batch_search_facets( array( 125 ), array(), array(), array( '80' ) );
	}
}
