<?php
/**
 * Tests for function reindex_all_outlet_products_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\init_products;
use function OutletPro\init_taxonomies;
use function OutletPro\seed_outlet_status_taxonomy;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Reindex_All_Outlet_Products_Hook extends WP_UnitTestCase {

	public function test_adding_search_index_term_queues_every_outlet_product(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Verify scheduling for each product status.
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		truncate_outlet_search_index_taxonomy();
		init_products();
		$products = array();
		foreach ( array( 'publish', 'draft', 'private', 'pending' ) as $status ) {
			$product = WC_Helper_Product::create_simple_product();
			$product->set_status( $status );
			$product->save();
			add_to_outlet( $product );
			as_unschedule_all_actions( 'outletpro_reindex_search_facets', array( $product->get_id() ), 'outletpro' );
			$products[] = $product;
		}
		$non_outlet = WC_Helper_Product::create_simple_product();

		// Act.
		wp_insert_term( 'New price tier', OUTLET_SEARCH_INDEX_TAXONOMY, array( 'slug' => 'outlet-price-25' ) );

		// Assert.
		foreach ( $products as $product ) {
			$actions = as_get_scheduled_actions(
				array(
					'hook'     => 'outletpro_reindex_search_facets',
					'args'     => array( $product->get_id() ),
					'group'    => 'outletpro',
					'status'   => ActionScheduler_Store::STATUS_PENDING,
					'per_page' => -1,
				),
				'ids'
			);
			$this->assertCount( 1, $actions );
		}
		$this->assertFalse( as_has_scheduled_action( 'outletpro_reindex_search_facets', array( $non_outlet->get_id() ), 'outletpro' ) );
	}

	public function test_editing_search_index_term_queues_every_outlet_product(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Verify scheduling for each product status.
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		truncate_outlet_search_index_taxonomy();
		init_products();
		$term     = wp_insert_term( 'Old price tier', OUTLET_SEARCH_INDEX_TAXONOMY, array( 'slug' => 'outlet-price-50' ) );
		$products = array();
		foreach ( array( 'publish', 'draft', 'private', 'pending' ) as $status ) {
			$product = WC_Helper_Product::create_simple_product();
			$product->set_status( $status );
			$product->save();
			add_to_outlet( $product );
			as_unschedule_all_actions( 'outletpro_reindex_search_facets', array( $product->get_id() ), 'outletpro' );
			$products[] = $product;
		}
		$non_outlet = WC_Helper_Product::create_simple_product();

		// Act.
		wp_update_term( $term['term_id'], OUTLET_SEARCH_INDEX_TAXONOMY, array( 'slug' => 'outlet-price-25' ) );

		// Assert.
		foreach ( $products as $product ) {
			$actions = as_get_scheduled_actions(
				array(
					'hook'     => 'outletpro_reindex_search_facets',
					'args'     => array( $product->get_id() ),
					'group'    => 'outletpro',
					'status'   => ActionScheduler_Store::STATUS_PENDING,
					'per_page' => -1,
				),
				'ids'
			);
			$this->assertCount( 1, $actions );
		}
		$this->assertFalse( as_has_scheduled_action( 'outletpro_reindex_search_facets', array( $non_outlet->get_id() ), 'outletpro' ) );
	}

	public function test_deleting_search_index_term_queues_every_outlet_product(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Verify scheduling for each product status.
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		truncate_outlet_search_index_taxonomy();
		init_products();
		$term     = wp_insert_term( 'Old price tier', OUTLET_SEARCH_INDEX_TAXONOMY, array( 'slug' => 'outlet-price-50' ) );
		$products = array();
		foreach ( array( 'publish', 'draft', 'private', 'pending' ) as $status ) {
			$product = WC_Helper_Product::create_simple_product();
			$product->set_status( $status );
			$product->save();
			add_to_outlet( $product );
			as_unschedule_all_actions( 'outletpro_reindex_search_facets', array( $product->get_id() ), 'outletpro' );
			$products[] = $product;
		}
		$non_outlet = WC_Helper_Product::create_simple_product();

		// Act.
		wp_delete_term( $term['term_id'], OUTLET_SEARCH_INDEX_TAXONOMY );

		// Assert.
		foreach ( $products as $product ) {
			$actions = as_get_scheduled_actions(
				array(
					'hook'     => 'outletpro_reindex_search_facets',
					'args'     => array( $product->get_id() ),
					'group'    => 'outletpro',
					'status'   => ActionScheduler_Store::STATUS_PENDING,
					'per_page' => -1,
				),
				'ids'
			);
			$this->assertCount( 1, $actions );
		}
		$this->assertFalse( as_has_scheduled_action( 'outletpro_reindex_search_facets', array( $non_outlet->get_id() ), 'outletpro' ) );
	}

	public function test_unrelated_term_changes_do_not_queue_outlet_products(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		init_products();
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		as_unschedule_all_actions( 'outletpro_reindex_search_facets', array( $product->get_id() ), 'outletpro' );

		// Act.
		$term = wp_insert_term( 'Unrelated category', 'product_cat' );
		wp_update_term( $term['term_id'], 'product_cat', array( 'name' => 'Updated category' ) );
		wp_delete_term( $term['term_id'], 'product_cat' );

		// Assert.
		$this->assertFalse( as_has_scheduled_action( 'outletpro_reindex_search_facets', array( $product->get_id() ), 'outletpro' ) );
	}
}
