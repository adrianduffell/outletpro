<?php
/**
 * Tests for reindex_search_facets_batch().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\init_products;
use function OutletPro\init_taxonomies;
use function OutletPro\reindex_search_facets_batch;
use function OutletPro\seed_outlet_search_index_taxonomy;
use function OutletPro\seed_outlet_status_taxonomy;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Reindex_Search_Facets_Batch extends WP_UnitTestCase {

	public function test_reindexes_each_supplied_product_object(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		seed_outlet_search_index_taxonomy();
		$first = WC_Helper_Product::create_simple_product();
		add_to_outlet( $first );
		$first->set_regular_price( '100' );
		$first->set_price( '20' );
		$second = WC_Helper_Product::create_simple_product();
		add_to_outlet( $second );
		$second->set_regular_price( '100' );
		$second->set_price( '40' );

		// Act.
		reindex_search_facets_batch( array( $first, $second ) );

		// Assert.
		$this->assertSame( array( 'outlet-discount-70', 'outlet-price-25' ), wp_get_object_terms( $first->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) ) );
		$this->assertSame( array( 'outlet-discount-50', 'outlet-price-50' ), wp_get_object_terms( $second->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) ) );
	}

	public function test_scheduled_batch_skips_deleted_products_and_reindexes_remaining_ids(): void {
		// Arrange.
		init_taxonomies();
		init_products();
		seed_outlet_status_taxonomy();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		seed_outlet_search_index_taxonomy();
		$deleted = WC_Helper_Product::create_simple_product();
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$action_id = as_enqueue_async_action( 'outletpro_reindex_search_facets_batch', array( array( $deleted->get_id(), $product->get_id() ) ), 'outletpro' );
		$deleted->delete( true );
		$product->set_regular_price( '100' );
		$product->set_sale_price( '20' );
		$product->save();

		// Act.
		ActionScheduler::runner()->process_action( $action_id, 'test' );

		// Assert.
		$this->assertSame( ActionScheduler_Store::STATUS_COMPLETE, ActionScheduler::store()->get_status( $action_id ) );
		$this->assertSame( array( 'outlet-discount-70', 'outlet-price-25' ), wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) ) );
	}
}
