<?php
/**
 * Tests for add_outletpro_rest_param_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\register_outlet_status_taxonomy;

class Test_Add_Outletpro_Rest_Param_Hook extends WP_UnitTestCase {

	public function test_outletpro_param_is_in_product_collection_schema(): void {
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
		$this->assertArrayHasKey( 'outletpro', $args );
		$this->assertSame( 'boolean', $args['outletpro']['type'] );
	}

	public function test_rest_product_query_filter_is_registered(): void {
		// Assert.
		$this->assertSame( 10, has_filter( 'rest_product_query', 'OutletPro\handle_outletpro_rest_param' ) );
	}
}
