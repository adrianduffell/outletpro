<?php
/**
 * Tests for the seed_outlet_search_index_taxonomy function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\init_products;
use function OutletPro\init_taxonomies;
use function OutletPro\register_outlet_search_index_taxonomy;
use function OutletPro\seed_outlet_search_index_taxonomy;
use function OutletPro\seed_outlet_status_taxonomy;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Seed_Outlet_Search_Index_Taxonomy extends WP_UnitTestCase {

	public function test_seeds_usd_price_search_indexes(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );

		// Act.
		seed_outlet_search_index_taxonomy();

		// Assert.
		$this->assertSame(
			array( 'outlet-discount-30', 'outlet-discount-50', 'outlet-discount-70', 'outlet-price-10', 'outlet-price-25', 'outlet-price-50' ),
			get_terms(
				array(
					'taxonomy'   => OUTLET_SEARCH_INDEX_TAXONOMY,
					'hide_empty' => false,
					'fields'     => 'slugs',
				)
			)
		);
	}

	public function test_seeds_aud_price_search_indexes(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'AUD' );

		// Act.
		seed_outlet_search_index_taxonomy();

		// Assert.
		$this->assertSame(
			array( 'outlet-discount-30', 'outlet-discount-50', 'outlet-discount-70', 'outlet-price-15', 'outlet-price-30', 'outlet-price-50' ),
			get_terms(
				array(
					'taxonomy'   => OUTLET_SEARCH_INDEX_TAXONOMY,
					'hide_empty' => false,
					'fields'     => 'slugs',
				)
			)
		);
	}

	public function test_seeds_jpy_price_search_indexes(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'JPY' );

		// Act.
		seed_outlet_search_index_taxonomy();

		// Assert.
		$this->assertSame(
			array( 'outlet-discount-30', 'outlet-discount-50', 'outlet-discount-70', 'outlet-price-1000', 'outlet-price-3000', 'outlet-price-5000' ),
			get_terms(
				array(
					'taxonomy'   => OUTLET_SEARCH_INDEX_TAXONOMY,
					'hide_empty' => false,
					'fields'     => 'slugs',
				)
			)
		);
	}

	public function test_uses_usd_price_search_indexes_for_unknown_currency(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'XYZ' );

		// Act.
		seed_outlet_search_index_taxonomy();

		// Assert.
		$this->assertSame(
			array( 'outlet-discount-30', 'outlet-discount-50', 'outlet-discount-70', 'outlet-price-10', 'outlet-price-25', 'outlet-price-50' ),
			get_terms(
				array(
					'taxonomy'   => OUTLET_SEARCH_INDEX_TAXONOMY,
					'hide_empty' => false,
					'fields'     => 'slugs',
				)
			)
		);
	}

	public function test_skips_seeding_and_reindexing_when_any_term_exists(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'custom', OUTLET_SEARCH_INDEX_TAXONOMY );
		as_unschedule_all_actions( 'outletpro_reindex_search_facets_batch', null, 'outletpro' );
		seed_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );

		// Act.
		seed_outlet_search_index_taxonomy();

		// Assert.
		$this->assertSame(
			array( 'custom' ),
			get_terms(
				array(
					'taxonomy'   => OUTLET_SEARCH_INDEX_TAXONOMY,
					'hide_empty' => false,
					'fields'     => 'slugs',
				)
			)
		);
		$this->assertFalse( as_has_scheduled_action( 'outletpro_reindex_search_facets_batch', null, 'outletpro' ) );
		$this->assertSame( 10, has_action( 'created_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\\reindex_all_outlet_products_hook' ) );
		$this->assertSame( 10, has_action( 'edited_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\\reindex_all_outlet_products_hook' ) );
		$this->assertSame( 10, has_action( 'delete_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\\reindex_all_outlet_products_hook' ) );
	}

	public function test_seeding_queues_one_reindex_per_product_and_restores_term_hooks(): void {
		// Arrange.
		init_taxonomies();
		init_products();
		seed_outlet_status_taxonomy();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		as_unschedule_all_actions( 'outletpro_reindex_search_facets', array( $product->get_id() ), 'outletpro' );

		// Act.
		seed_outlet_search_index_taxonomy();

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
		$this->assertSame( 10, has_action( 'created_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook' ) );
		$this->assertSame( 10, has_action( 'edited_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook' ) );
		$this->assertSame( 10, has_action( 'delete_' . OUTLET_SEARCH_INDEX_TAXONOMY, 'OutletPro\reindex_all_outlet_products_hook' ) );
	}
}
