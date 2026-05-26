<?php
/**
 * Tests for handle_wc_outlet_rest_param().
 *
 * @package WC_Outlet
 */

use function WC_Outlet\add_to_outlet;
use function WC_Outlet\handle_wc_outlet_rest_param;
use function WC_Outlet\register_outlet_status_taxonomy;
use const WC_Outlet\OUTLET_STATUS_TAXONOMY;

class Test_Handle_Wc_Outlet_Rest_Param extends WP_UnitTestCase {

	public function test_unfiltered_request_returns_all_products(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		$outlet_product     = WC_Helper_Product::create_simple_product();
		$non_outlet_product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $outlet_product );

		// Act.
		$request  = new WP_REST_Request( 'GET', '/wc/v3/products' );
		$response = rest_do_request( $request );

		// Assert.
		$ids = wp_list_pluck( $response->get_data(), 'id' );
		$this->assertContains( $outlet_product->get_id(), $ids );
		$this->assertContains( $non_outlet_product->get_id(), $ids );
	}

	public function test_wc_outlet_param_filters_to_outlet_products_only(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		$outlet_product     = WC_Helper_Product::create_simple_product();
		$non_outlet_product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $outlet_product );

		// Act.
		$request = new WP_REST_Request( 'GET', '/wc/v3/products' );
		$request->set_param( 'wc_outlet', true );
		$response = rest_do_request( $request );

		// Assert.
		$ids = wp_list_pluck( $response->get_data(), 'id' );
		$this->assertContains( $outlet_product->get_id(), $ids );
		$this->assertNotContains( $non_outlet_product->get_id(), $ids );
	}

	public function test_false_wc_outlet_param_returns_all_products(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		$outlet_product     = WC_Helper_Product::create_simple_product();
		$non_outlet_product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $outlet_product );

		// Act.
		$request = new WP_REST_Request( 'GET', '/wc/v3/products' );
		$request->set_param( 'wc_outlet', false );
		$response = rest_do_request( $request );

		// Assert.
		$ids = wp_list_pluck( $response->get_data(), 'id' );
		$this->assertContains( $outlet_product->get_id(), $ids );
		$this->assertContains( $non_outlet_product->get_id(), $ids );
	}

	public function test_rest_product_query_is_unchanged_when_wc_outlet_param_is_absent(): void {
		// Arrange.
		$args    = array( 'post_type' => 'product' );
		$request = new WP_REST_Request( 'GET', '/wp/v2/products' );
		$this->assertArrayNotHasKey( 'tax_query', $args );

		// Act.
		$result = handle_wc_outlet_rest_param( $args, $request );

		// Assert.
		$this->assertArrayNotHasKey( 'tax_query', $result );
	}

	public function test_rest_product_query_adds_tax_query_when_wc_outlet_is_true(): void {
		// Arrange.
		$args    = array(
			'post_type' => 'product',
			'tax_query' => array(),
		);
		$request = new WP_REST_Request( 'GET', '/wp/v2/products' );
		$request->set_param( 'wc_outlet', true );
		$this->assertCount( 0, $args['tax_query'] );

		// Act.
		$result = handle_wc_outlet_rest_param( $args, $request );

		// Assert.
		$this->assertArrayHasKey( 'tax_query', $result );
		$this->assertContains(
			array(
				'taxonomy' => OUTLET_STATUS_TAXONOMY,
				'field'    => 'slug',
				'terms'    => array( 'outlet' ),
			),
			$result['tax_query']
		);
	}

	public function test_rest_product_query_is_unchanged_when_wc_outlet_is_false(): void {
		// Arrange.
		$args    = array(
			'post_type' => 'product',
			'tax_query' => array(),
		);
		$request = new WP_REST_Request( 'GET', '/wp/v2/products' );
		$request->set_param( 'wc_outlet', false );
		$this->assertCount( 0, $args['tax_query'] );

		// Act.
		$result = handle_wc_outlet_rest_param( $args, $request );

		// Assert.
		$this->assertCount( 0, $result['tax_query'] );
	}
}
