<?php
/**
 * Tests for the outlet search index indexing tool.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\init_tools;
use function OutletPro\register_outlet_search_index_taxonomy;
use function OutletPro\register_outlet_status_taxonomy;
use function OutletPro\run_index_outlet_search_indexes_tool;
use function OutletPro\seed_outlet_status_taxonomy;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Index_Outlet_Search_Indexes_Tool extends WP_UnitTestCase {

	public function test_registers_tool_with_callable_callback_and_preserves_existing_tools(): void {
		// Arrange.
		init_tools();
		$existing = array( 'example' => array( 'name' => 'Example' ) );

		// Act.
		$tools = apply_filters( 'woocommerce_debug_tools', $existing );

		// Assert.
		$this->assertSame( $existing['example'], $tools['example'] );
		$this->assertSame( 'Index outlet search indexes', $tools['index_outletpro_search_indexes']['name'] );
		$this->assertSame( 'OutletPro\\run_index_outlet_search_indexes_tool', $tools['index_outletpro_search_indexes']['callback'] );
		$this->assertIsCallable( $tools['index_outletpro_search_indexes']['callback'] );
	}

	public function test_indexes_only_published_outlet_products_and_replaces_stale_search_indexes(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		register_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		seed_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		$product->set_regular_price( '100' );
		$product->set_sale_price( '20' );
		$product->set_price( '20' );
		$product->save();
		add_to_outlet( $product );
		wp_set_object_terms( $product->get_id(), 'outlet-price-100', OUTLET_SEARCH_INDEX_TAXONOMY );
		$draft = WC_Helper_Product::create_simple_product();
		$draft->set_status( 'draft' );
		$draft->save();
		add_to_outlet( $draft );
		$draft_search_indexes = wp_get_object_terms( $draft->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) );
		$other                = WC_Helper_Product::create_simple_product();

		// Act.
		$result = run_index_outlet_search_indexes_tool();
		run_index_outlet_search_indexes_tool();

		// Assert.
		$this->assertSame( 'Outlet Pro search indexes indexed.', $result );
		$this->assertSame(
			array( 'outlet-discount-70', 'outlet-price-25' ),
			wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) )
		);
		$this->assertSame( $draft_search_indexes, wp_get_object_terms( $draft->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) ) );
		$this->assertSame( array(), wp_get_object_terms( $other->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY ) );
	}

	public function test_indexes_products_beyond_first_batch(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Arrange and assert each product in the batch.
		// Arrange.
		register_outlet_status_taxonomy();
		register_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );
		seed_outlet_status_taxonomy();
		$products = array();
		for ( $index = 0; $index < 101; ++$index ) {
			$product = WC_Helper_Product::create_simple_product();
			add_to_outlet( $product );
			$products[] = $product;
		}

		// Act.
		run_index_outlet_search_indexes_tool();

		// Assert.
		foreach ( $products as $product ) {
			$this->assertContains(
				'outlet-price-10',
				wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) )
			);
		}
	}

	public function test_succeeds_when_outlet_is_empty(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		register_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );

		// Act.
		$result = run_index_outlet_search_indexes_tool();

		// Assert.
		$this->assertSame( 'Outlet Pro search indexes indexed.', $result );
	}

	public function test_returns_failure_when_search_index_taxonomy_is_missing(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		unregister_taxonomy( OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		$result = run_index_outlet_search_indexes_tool();

		// Assert.
		$this->assertSame( 'Outlet Pro search indexes could not be indexed.', $result );
	}
}
