<?php
/**
 * Test the add_to_outlet function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\deinit_taxonomies;
use function OutletPro\init_taxonomies;
use function OutletPro\init_products;
use function OutletPro\seed_outlet_search_index_taxonomy;
use function OutletPro\seed_outlet_status_taxonomy;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;
use const OutletPro\OUTLET_STATUS_CANONICAL_TERM;
use const OutletPro\OUTLET_STATUS_TAXONOMY;

class Test_Add_To_Outlet extends WP_UnitTestCase {

	public function test_throws_exception_when_taxonomy_not_registered(): void {
		// Arrange.
		deinit_taxonomies();
		$product = \WC_Helper_Product::create_simple_product();

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		add_to_outlet( $product );
	}

	public function test_assigns_outlet_term_to_single_product(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();

		// Act.
		add_to_outlet( $product );

		// Assert.
		$terms = wp_get_object_terms( $product->get_id(), OUTLET_STATUS_TAXONOMY, array( 'fields' => 'names' ) );
		$this->assertContains( OUTLET_STATUS_CANONICAL_TERM, $terms );
	}

	public function test_throws_exception_on_insert_term_failure(): void {
		// Arrange.
		init_taxonomies();
		foreach ( get_terms(
			array(
				'taxonomy'   => OUTLET_STATUS_TAXONOMY,
				'hide_empty' => false,
			)
		) as $term ) {
			wp_delete_term( $term->term_id, OUTLET_STATUS_TAXONOMY );
		}
		$product = \WC_Helper_Product::create_simple_product();

		add_filter(
			'pre_insert_term',
			fn() => new WP_Error( 'simulated_error' ),
		);

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		add_to_outlet( $product );
	}

	public function test_schedules_one_search_index_update_without_another_product_save(): void {
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
		$product->set_price( '20' );
		$product->save();

		// Act.
		add_to_outlet( $product );
		add_to_outlet( $product );

		// Assert.
		$this->assertSame( array(), wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY ) );
		$action_ids = as_get_scheduled_actions(
			array(
				'hook'   => 'outletpro_reindex_search_facets',
				'args'   => array( $product->get_id() ),
				'status' => ActionScheduler_Store::STATUS_PENDING,
			),
			'ids'
		);
		$this->assertCount( 1, $action_ids );

		// Act.
		ActionScheduler::runner()->process_action( $action_ids[0], 'test' );

		// Assert.
		$this->assertSame( ActionScheduler_Store::STATUS_COMPLETE, ActionScheduler::store()->get_status( $action_ids[0] ) );
		$this->assertSame(
			array( 'outlet-discount-70', 'outlet-price-25' ),
			wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY, array( 'fields' => 'slugs' ) )
		);
	}
}
