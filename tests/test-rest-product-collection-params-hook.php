<?php
/**
 * Tests for the rest_product_collection_params_hook.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\register_clearance_status_taxonomy;

class Test_Rest_Product_Collection_Params_Hook extends WP_UnitTestCase {

	public function test_wc_clearance_param_is_in_product_collection_schema(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Act.
		$request  = new WP_REST_Request( 'OPTIONS', '/wc/v3/products' );
		$response = rest_do_request( $request );

		// Assert.
		$data = $response->get_data();
		$args = $data['endpoints'][0]['args'] ?? array();
		$this->assertArrayHasKey( 'wc_clearance', $args );
		$this->assertSame( 'boolean', $args['wc_clearance']['type'] );
	}
}
