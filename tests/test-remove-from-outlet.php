<?php
/**
 * Test the remove_from_outlet function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_taxonomies;
use function OutletPro\init_products;
use function OutletPro\init_taxonomies;
use function OutletPro\remove_from_outlet;
use function OutletPro\seed_outlet_status_taxonomy;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;
use const OutletPro\OUTLET_STATUS_CANONICAL_TERM;
use const OutletPro\OUTLET_STATUS_TAXONOMY;

class Test_Remove_From_Outlet extends WP_UnitTestCase {

	public function test_removes_product_from_outlet(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		wp_set_object_terms( $product->get_id(), OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY );

		// Act.
		remove_from_outlet( $product );

		// Assert.
		$terms = wp_get_object_terms( $product->get_id(), OUTLET_STATUS_TAXONOMY );
		$this->assertEmpty( $terms );
	}

	public function test_does_not_error_when_product_not_in_outlet(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();

		// Act & Assert (no exception should be thrown).
		remove_from_outlet( $product );
		$terms = wp_get_object_terms( $product->get_id(), OUTLET_STATUS_TAXONOMY );
		$this->assertEmpty( $terms );
	}

	public function test_throws_runtimeexception_when_taxonomy_does_not_exist(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		deinit_taxonomies();

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		remove_from_outlet( $product );
	}

	public function test_schedules_one_action_to_clear_product_search_index_terms(): void {
		// Arrange.
		init_taxonomies();
		init_products();
		seed_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		wp_set_object_terms( $product->get_id(), OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY );
		wp_set_object_terms( $product->get_id(), array( 'outlet-price-25', 'outlet-discount-70', 'custom-index' ), OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		remove_from_outlet( $product );
		remove_from_outlet( $product );

		// Assert.
		$this->assertCount( 3, wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY ) );
		$action_ids = as_get_scheduled_actions(
			array(
				'hook'   => 'outletpro_clear_search_index_facets',
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
		$this->assertSame( array(), wp_get_object_terms( $product->get_id(), OUTLET_SEARCH_INDEX_TAXONOMY ) );
	}
}
