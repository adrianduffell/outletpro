<?php
/**
 * Tests for add_wc_outlet_rest_param_hook().
 *
 * @package WC_Outlet
 */

use function WC_Outlet\register_outlet_status_taxonomy;

class Test_Add_Wc_Outlet_Rest_Param_Hook extends WP_UnitTestCase {

	public function test_wc_outlet_param_is_in_product_collection_schema(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Act.
		$request  = new WP_REST_Request( 'OPTIONS', '/wc/v3/products' );
		$response = rest_do_request( $request );

		// Assert.
		$data = $response->get_data();
		$args = $data['endpoints'][0]['args'] ?? array();
		$this->assertArrayHasKey( 'wc_outlet', $args );
		$this->assertSame( 'boolean', $args['wc_outlet']['type'] );
	}
}
