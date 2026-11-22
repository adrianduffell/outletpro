<?php
/**
 * Tests for batch_discount_tiers() function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_discount_tier;
use function OutletPro\add_to_outlet;
use function OutletPro\batch_discount_tiers;
use function OutletPro\get_discount_tiers;
use function OutletPro\get_price_tiers;
use function OutletPro\init_products;
use function OutletPro\init_taxonomies;
use function OutletPro\seed_outlet_status_taxonomy;
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

	public function test_reindexes_on_shutdown(): void {
		// Arrange.
		remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 ); // Preserve PHPUnit's output buffer.
		remove_action( 'shutdown', 'OutletPro\reindex_all_outlet_products_hook', 10 );
		init_taxonomies();
		init_products();
		seed_outlet_status_taxonomy();
		truncate_outlet_search_index_taxonomy();
		add_discount_tier( 50 );
		add_discount_tier( 70 );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		as_unschedule_all_actions( 'outletpro_reindex_search_facets_batch', null, 'outletpro' );

		// Act.
		batch_discount_tiers( array( 80 ), array( 50 => 60 ), array( 70 ) );
		do_action( 'shutdown' );

		// Assert.
		$this->assertTrue( as_has_scheduled_action( 'outletpro_reindex_search_facets_batch', array( array( $product->get_id() ) ), 'outletpro' ) );
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
