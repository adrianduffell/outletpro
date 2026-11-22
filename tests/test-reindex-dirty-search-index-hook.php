<?php
/**
 * Tests for reindex_dirty_search_index_hook() function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\batch_discount_tiers;
use function OutletPro\batch_price_tiers;
use function OutletPro\init_products;
use function OutletPro\init_taxonomies;
use function OutletPro\seed_outlet_status_taxonomy;
use function OutletPro\truncate_outlet_search_index_taxonomy;

class Test_Reindex_Dirty_Search_Index_Hook extends WP_UnitTestCase {

	public function test_skips_reindexing_when_clean(): void {
		// Arrange.
		remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 ); // Preserve PHPUnit's output buffer.
		init_taxonomies();
		init_products();
		seed_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		as_unschedule_all_actions( 'outletpro_reindex_search_facets_batch', null, 'outletpro' );
		delete_option( 'outletpro_search_index_dirty' );

		// Act.
		do_action( 'shutdown' );

		// Assert.
		$this->assertFalse( as_has_scheduled_action( 'outletpro_reindex_search_facets_batch', null, 'outletpro' ) );
	}

	public function test_reindexes_dirty_search_index(): void {
		// Arrange.
		remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 ); // Preserve PHPUnit's output buffer.
		init_taxonomies();
		init_products();
		seed_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		as_unschedule_all_actions( 'outletpro_reindex_search_facets_batch', null, 'outletpro' );
		update_option( 'outletpro_search_index_dirty', 'yes', false );

		// Act.
		do_action( 'shutdown' );
		do_action( 'shutdown' );

		// Assert.
		$actions = as_get_scheduled_actions(
			array(
				'hook'   => 'outletpro_reindex_search_facets_batch',
				'group'  => 'outletpro',
				'status' => ActionScheduler_Store::STATUS_PENDING,
			)
		);
		$this->assertCount( 1, $actions );
		$this->assertSame( array( array( $product->get_id() ) ), reset( $actions )->get_args() );
		$this->assertFalse( get_option( 'outletpro_search_index_dirty' ) );
	}

	public function test_reindexes_once_after_both_batches(): void {
		// Arrange.
		remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 ); // Preserve PHPUnit's output buffer.
		init_taxonomies();
		init_products();
		seed_outlet_status_taxonomy();
		truncate_outlet_search_index_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		as_unschedule_all_actions( 'outletpro_reindex_search_facets_batch', null, 'outletpro' );
		delete_option( 'outletpro_search_index_dirty' );

		// Act.
		batch_price_tiers( array( 50, 100 ) );
		batch_discount_tiers( array( 30, 50, 70 ) );
		do_action( 'shutdown' );

		// Assert.
		$actions = as_get_scheduled_actions(
			array(
				'hook'   => 'outletpro_reindex_search_facets_batch',
				'group'  => 'outletpro',
				'status' => ActionScheduler_Store::STATUS_PENDING,
			)
		);
		$this->assertCount( 1, $actions );
		$this->assertSame( array( array( $product->get_id() ) ), reset( $actions )->get_args() );
		$this->assertFalse( get_option( 'outletpro_search_index_dirty' ) );
	}
}
