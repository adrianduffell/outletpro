<?php
/**
 * Tests for handle_wc_outlet_rest_param().
 *
 * @package WC_Outlet
 */

use function WC_Outlet\add_to_outlet;
use function WC_Outlet\register_outlet_status_taxonomy;

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
}
