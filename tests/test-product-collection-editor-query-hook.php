<?php
/**
 * Tests for product_collection_editor_query_hook().
 *
 * @package WC_Outlet
 */

use function WC_Outlet\init_product_collection;
use const WC_Outlet\OUTLET_STATUS_TAXONOMY;

class Test_Product_Collection_Editor_Query_Hook extends WP_UnitTestCase {

	public function test_query_is_unchanged_when_is_product_collection_block_param_is_absent(): void {
		// Arrange.
		remove_all_filters( 'rest_product_query' );
		init_product_collection();
		$args     = array( 'post_type' => 'product' );
		$request  = new WP_REST_Request( 'GET', '/wc/v3/products' );
		$expected = $args;

		// Act.
		$result = apply_filters( 'rest_product_query', $args, $request );

		// Assert.
		$this->assertSame( $expected, $result );
	}

	public function test_query_is_unchanged_when_is_product_collection_block_param_is_false(): void {
		// Arrange.
		remove_all_filters( 'rest_product_query' );
		init_product_collection();
		$args    = array( 'post_type' => 'product' );
		$request = new WP_REST_Request( 'GET', '/wc/v3/products' );
		$request->set_param( 'isProductCollectionBlock', false );
		$expected = $args;

		// Act.
		$result = apply_filters( 'rest_product_query', $args, $request );

		// Assert.
		$this->assertSame( $expected, $result );
	}

	public function test_query_is_unchanged_when_product_collection_query_context_is_absent(): void {
		// Arrange.
		remove_all_filters( 'rest_product_query' );
		init_product_collection();
		$args    = array( 'post_type' => 'product' );
		$request = new WP_REST_Request( 'GET', '/wc/v3/products' );
		$request->set_param( 'isProductCollectionBlock', true );
		$expected = $args;

		// Act.
		$result = apply_filters( 'rest_product_query', $args, $request );

		// Assert.
		$this->assertSame( $expected, $result );
	}

	public function test_query_is_unchanged_when_collection_is_different(): void {
		// Arrange.
		remove_all_filters( 'rest_product_query' );
		init_product_collection();
		$args    = array( 'post_type' => 'product' );
		$request = new WP_REST_Request( 'GET', '/wc/v3/products' );
		$request->set_param( 'isProductCollectionBlock', true );
		$request->set_param( 'productCollectionQueryContext', array( 'collection' => 'wc-outlet/product-collection/other' ) );
		$expected = $args;

		// Act.
		$result = apply_filters( 'rest_product_query', $args, $request );

		// Assert.
		$this->assertSame( $expected, $result );
	}

	public function test_tax_query_is_added_for_outlet_collection(): void {
		// Arrange.
		remove_all_filters( 'rest_product_query' );
		init_product_collection();
		$args    = array( 'post_type' => 'product' );
		$request = new WP_REST_Request( 'GET', '/wc/v3/products' );
		$request->set_param( 'isProductCollectionBlock', true );
		$request->set_param( 'productCollectionQueryContext', array( 'collection' => 'wc-outlet/product-collection/outlet' ) );

		// Act.
		$result = apply_filters( 'rest_product_query', $args, $request );

		// Assert.
		$this->assertArrayHasKey( 'tax_query', $result );
		$this->assertCount( 1, $result['tax_query'] );
		$this->assertSame( OUTLET_STATUS_TAXONOMY, $result['tax_query'][0]['taxonomy'] );
		$this->assertSame( 'slug', $result['tax_query'][0]['field'] );
		$this->assertSame( array( 'outlet' ), $result['tax_query'][0]['terms'] );
	}

	public function test_existing_tax_query_entries_are_preserved(): void {
		// Arrange.
		remove_all_filters( 'rest_product_query' );
		init_product_collection();
		$existing_tax_clause = array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => array( 'clothing' ),
		);
		$args                = array(
			'post_type' => 'product',
			'tax_query' => array( $existing_tax_clause ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		);
		$request             = new WP_REST_Request( 'GET', '/wc/v3/products' );
		$request->set_param( 'isProductCollectionBlock', true );
		$request->set_param( 'productCollectionQueryContext', array( 'collection' => 'wc-outlet/product-collection/outlet' ) );

		// Act.
		$result = apply_filters( 'rest_product_query', $args, $request );

		// Assert.
		$this->assertCount( 2, $result['tax_query'] );
		$this->assertSame( $existing_tax_clause, $result['tax_query'][0] );
		$this->assertSame( OUTLET_STATUS_TAXONOMY, $result['tax_query'][1]['taxonomy'] );
	}

	public function test_filter_is_registered_by_init_product_collection(): void {
		// Arrange.
		remove_all_filters( 'rest_product_query' );

		// Act.
		init_product_collection();

		// Assert.
		$this->assertSame( 10, has_filter( 'rest_product_query', 'WC_Outlet\product_collection_editor_query_hook' ) );
	}
}
