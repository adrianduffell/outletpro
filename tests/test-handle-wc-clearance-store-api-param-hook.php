<?php
/**
 * Tests for handle_wc_clearance_store_api_param_hook().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\register_clearance_status_taxonomy;
use const WC_Clearance\CLEARANCE_STATUS_CANONICAL_TERM;
use const WC_Clearance\CLEARANCE_STATUS_TAXONOMY;

class Test_Handle_Wc_Clearance_Store_Api_Param_Hook extends WP_UnitTestCase {

	public function test_args_unchanged_when_wc_clearance_absent(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$args    = array( 'post_type' => 'product' );
		$request = new WP_REST_Request( 'GET', '/wc/store/v1/products' );

		// Act.
		$result = apply_filters( 'woocommerce_store_api_product_query', $args, $request );

		// Assert.
		$this->assertSame( $args, $result );
	}

	public function test_args_unchanged_when_wc_clearance_is_false(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$args    = array( 'post_type' => 'product' );
		$request = new WP_REST_Request( 'GET', '/wc/store/v1/products' );
		$request->set_param( 'wc_clearance', false );

		// Act.
		$result = apply_filters( 'woocommerce_store_api_product_query', $args, $request );

		// Assert.
		$this->assertSame( $args, $result );
	}

	public function test_tax_query_added_when_wc_clearance_is_true(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$args    = array( 'post_type' => 'product' );
		$request = new WP_REST_Request( 'GET', '/wc/store/v1/products' );
		$request->set_param( 'wc_clearance', true );

		// Act.
		$result = apply_filters( 'woocommerce_store_api_product_query', $args, $request );

		// Assert.
		$this->assertArrayHasKey( 'tax_query', $result );
		$tax_query = $result['tax_query'];
		$this->assertCount( 1, $tax_query );
		$this->assertSame( CLEARANCE_STATUS_TAXONOMY, $tax_query[0]['taxonomy'] );
		$this->assertSame( 'slug', $tax_query[0]['field'] );
		$this->assertContains( CLEARANCE_STATUS_CANONICAL_TERM, $tax_query[0]['terms'] );
	}

	public function test_existing_tax_query_is_preserved(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$existing_clause = array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => array( 'shoes' ),
		);
		$args            = array(
			'post_type' => 'product',
			'tax_query' => array( $existing_clause ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		);
		$request         = new WP_REST_Request( 'GET', '/wc/store/v1/products' );
		$request->set_param( 'wc_clearance', true );

		// Act.
		$result = apply_filters( 'woocommerce_store_api_product_query', $args, $request );

		// Assert.
		$this->assertCount( 2, $result['tax_query'] );
		$this->assertContains( $existing_clause, $result['tax_query'] );
	}
}
