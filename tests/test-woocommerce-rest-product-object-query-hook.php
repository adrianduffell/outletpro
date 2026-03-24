<?php
/**
 * Tests for the wc_clearance_status REST API filter.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_to_clearance;
use function WC_Clearance\register_clearance_status_taxonomy;
use const WC_Clearance\CLEARANCE_STATUS_CANONICAL_TERM;

class Test_WooCommerce_Rest_Product_Object_Query_Hook extends WP_UnitTestCase {

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

	public function test_wc_clearance_status_param_filters_to_clearance_products_only(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		$clearance_product     = WC_Helper_Product::create_simple_product();
		$non_clearance_product = WC_Helper_Product::create_simple_product();
		add_to_clearance( $clearance_product );

		// Act.
		$request = new WP_REST_Request( 'GET', '/wc/v3/products' );
		$request->set_param( 'wc_clearance_status', CLEARANCE_STATUS_CANONICAL_TERM );
		$response = rest_do_request( $request );

		// Assert.
		$ids = wp_list_pluck( $response->get_data(), 'id' );
		$this->assertContains( $clearance_product->get_id(), $ids );
		$this->assertNotContains( $non_clearance_product->get_id(), $ids );
	}

	public function test_empty_wc_clearance_status_param_returns_all_products(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		$clearance_product     = WC_Helper_Product::create_simple_product();
		$non_clearance_product = WC_Helper_Product::create_simple_product();
		add_to_clearance( $clearance_product );

		// Act.
		$request = new WP_REST_Request( 'GET', '/wc/v3/products' );
		$request->set_param( 'wc_clearance_status', '' );
		$response = rest_do_request( $request );

		// Assert.
		$ids = wp_list_pluck( $response->get_data(), 'id' );
		$this->assertContains( $clearance_product->get_id(), $ids );
		$this->assertContains( $non_clearance_product->get_id(), $ids );
	}
}
