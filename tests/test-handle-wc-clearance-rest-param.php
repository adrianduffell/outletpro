<?php
/**
 * Tests for handle_wc_clearance_rest_param().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_to_clearance;
use function WC_Clearance\register_clearance_status_taxonomy;

class Test_Handle_Wc_Clearance_Rest_Param extends WP_UnitTestCase {

	public function test_unfiltered_request_returns_all_products(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		$clearance_product     = WC_Helper_Product::create_simple_product();
		$non_clearance_product = WC_Helper_Product::create_simple_product();
		add_to_clearance( $clearance_product );

		// Act.
		$request  = new WP_REST_Request( 'GET', '/wc/v3/products' );
		$response = rest_do_request( $request );

		// Assert.
		$ids = wp_list_pluck( $response->get_data(), 'id' );
		$this->assertContains( $clearance_product->get_id(), $ids );
		$this->assertContains( $non_clearance_product->get_id(), $ids );
	}

	public function test_wc_clearance_param_filters_to_clearance_products_only(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		$clearance_product     = WC_Helper_Product::create_simple_product();
		$non_clearance_product = WC_Helper_Product::create_simple_product();
		add_to_clearance( $clearance_product );

		// Act.
		$request = new WP_REST_Request( 'GET', '/wc/v3/products' );
		$request->set_param( 'wc_clearance', true );
		$response = rest_do_request( $request );

		// Assert.
		$ids = wp_list_pluck( $response->get_data(), 'id' );
		$this->assertContains( $clearance_product->get_id(), $ids );
		$this->assertNotContains( $non_clearance_product->get_id(), $ids );
	}

	public function test_false_wc_clearance_param_returns_all_products(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		$clearance_product     = WC_Helper_Product::create_simple_product();
		$non_clearance_product = WC_Helper_Product::create_simple_product();
		add_to_clearance( $clearance_product );

		// Act.
		$request = new WP_REST_Request( 'GET', '/wc/v3/products' );
		$request->set_param( 'wc_clearance', false );
		$response = rest_do_request( $request );

		// Assert.
		$ids = wp_list_pluck( $response->get_data(), 'id' );
		$this->assertContains( $clearance_product->get_id(), $ids );
		$this->assertContains( $non_clearance_product->get_id(), $ids );
	}
}
