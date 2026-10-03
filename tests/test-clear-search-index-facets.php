<?php
/**
 * Tests for the clear_search_index_facets function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\init_products;
use function OutletPro\init_taxonomies;
use function OutletPro\remove_from_outlet;
use function OutletPro\seed_outlet_search_index_taxonomy;
use function OutletPro\seed_outlet_status_taxonomy;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Clear_Search_Index_Facets extends WP_UnitTestCase {

	public function test_preserves_facets_when_product_is_added_back_before_clearing(): void {
		// Arrange.
		init_taxonomies();
		init_products();
		seed_outlet_status_taxonomy();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		seed_outlet_search_index_taxonomy();

		$product = WC_Helper_Product::create_simple_product();
		$product->set_regular_price( '100' );
		$product->set_sale_price( '20' );
		$product->save();
		add_to_outlet( $product );
		remove_from_outlet( $product );
		$clear_action_id = as_next_scheduled_action( 'outletpro_clear_search_index_facets', array( $product->get_id() ), 'outletpro' );
		add_to_outlet( $product );
		$reindex_action_id = as_next_scheduled_action( 'outletpro_reindex_search_facets', array( $product->get_id() ), 'outletpro' );

		// Act.
		ActionScheduler::runner()->process_action( $reindex_action_id, 'test' );
		ActionScheduler::runner()->process_action( $clear_action_id, 'test' );

		// Assert.
		$this->assertSame( array( 'outlet-discount-70', 'outlet-price-25' ), wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) ) );
		$this->assertSame( ActionScheduler_Store::STATUS_COMPLETE, ActionScheduler::store()->get_status( $clear_action_id ) );
	}

	public function test_pending_reindex_does_not_restore_facets_after_removal(): void {
		// Arrange.
		init_taxonomies();
		init_products();
		seed_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		wp_set_object_terms( $product->get_id(), 'outlet-price-25', OUTLET_SEARCH_INDEX_TAXONOMY );
		$reindex_action_id = as_next_scheduled_action( 'outletpro_reindex_search_facets', array( $product->get_id() ), 'outletpro' );
		remove_from_outlet( $product );
		$clear_action_id = as_next_scheduled_action( 'outletpro_clear_search_index_facets', array( $product->get_id() ), 'outletpro' );

		// Act.
		ActionScheduler::runner()->process_action( $clear_action_id, 'test' );
		ActionScheduler::runner()->process_action( $reindex_action_id, 'test' );

		// Assert.
		$this->assertSame( array(), wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY ) );
		$this->assertSame( ActionScheduler_Store::STATUS_COMPLETE, ActionScheduler::store()->get_status( $reindex_action_id ) );
	}

	public function test_scheduled_action_skips_a_deleted_product(): void {
		// Arrange.
		init_taxonomies();
		init_products();
		seed_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		remove_from_outlet( $product );
		$action_id = as_next_scheduled_action( 'outletpro_clear_search_index_facets', array( $product->get_id() ), 'outletpro' );
		$product->delete( true );

		// Act.
		ActionScheduler::runner()->process_action( $action_id, 'test' );

		// Assert.
		$this->assertSame( ActionScheduler_Store::STATUS_COMPLETE, ActionScheduler::store()->get_status( $action_id ) );
	}
}
