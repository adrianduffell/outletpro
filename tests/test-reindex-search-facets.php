<?php
/**
 * Tests for the reindex_search_facets function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\init_products;
use function OutletPro\init_taxonomies;
use function OutletPro\reindex_search_facets;
use function OutletPro\seed_outlet_search_index_taxonomy;
use function OutletPro\seed_outlet_status_taxonomy;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Reindex_Search_Facets extends WP_UnitTestCase {

	public function test_clears_all_facets_for_non_outlet_product(): void {
		// Arrange.
		init_taxonomies();
		$product = WC_Helper_Product::create_simple_product();
		wp_set_object_terms( $product->get_id(), array( 'outlet-price-25', 'outlet-discount-70', 'custom-index' ), OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		reindex_search_facets( $product );

		// Assert.
		$this->assertSame( array(), wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY ) );
	}

	public function test_reindexes_using_the_supplied_product_object(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		seed_outlet_search_index_taxonomy();

		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$product->set_regular_price( '100' );
		$product->set_price( '20' );

		// Act.
		reindex_search_facets( $product );

		// Assert.
		$this->assertSame( array( 'outlet-discount-70', 'outlet-price-25' ), wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) ) );
	}

	public function test_scheduled_action_reindexes_using_current_product_data(): void {
		// Arrange.
		init_taxonomies();
		init_products();
		seed_outlet_status_taxonomy();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		seed_outlet_search_index_taxonomy();

		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$action_id = as_enqueue_async_action( 'outletpro_reindex_search_facets', array( $product->get_id() ), 'outletpro' );

		$product->set_regular_price( '100' );
		$product->set_sale_price( '20' );
		$product->save();
		wp_set_object_terms( $product->get_id(), 'outlet-price-10', OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		ActionScheduler::runner()->process_action( $action_id, 'test' );

		// Assert.
		$this->assertSame( array( 'outlet-discount-70', 'outlet-price-25' ), wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) ) );
		$this->assertSame( ActionScheduler_Store::STATUS_COMPLETE, ActionScheduler::store()->get_status( $action_id ) );
	}

	public function test_scheduled_action_skips_a_deleted_product(): void {
		// Arrange.
		init_taxonomies();
		init_products();

		$product   = WC_Helper_Product::create_simple_product();
		$action_id = as_enqueue_async_action( 'outletpro_reindex_search_facets', array( $product->get_id() ), 'outletpro' );
		$product->delete( true );

		// Act.
		ActionScheduler::runner()->process_action( $action_id, 'test' );

		// Assert.
		$this->assertSame( ActionScheduler_Store::STATUS_COMPLETE, ActionScheduler::store()->get_status( $action_id ) );
	}
}
