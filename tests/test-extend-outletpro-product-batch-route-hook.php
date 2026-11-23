<?php
/**
 * WooCommerce route extension tests.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\init_taxonomies;

class Test_Extend_Outletpro_Product_Batch_Route_Hook extends WP_UnitTestCase {
	public function test_publishes_membership_in_batch_item_schemas(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		add_filter( 'rest_pre_dispatch', 'rest_handle_options_request', 10, 3 );
		// Act.
		$data = rest_do_request( new WP_REST_Request( 'OPTIONS', '/wc/v3/products/batch' ) )->get_data();
		// Assert.
		$this->assertSame( 'object', $data['schema']['properties']['create']['items']['properties']['outletpro']['type'] );
		$this->assertSame( 'object', $data['schema']['properties']['update']['items']['properties']['outletpro']['type'] );
	}

	public function test_retains_authentication_and_batch_limit(): void {
		// Arrange.
		wp_set_current_user( 0 );
		// Act / Assert.
		$this->assertSame( 401, rest_do_request( new WP_REST_Request( 'GET', '/wc/v3/products' ) )->get_status() );
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'subscriber' ) ) );
		$this->assertSame( 403, rest_do_request( new WP_REST_Request( 'GET', '/wc/v3/products' ) )->get_status() );
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$request = new WP_REST_Request( 'POST', '/wc/v3/products/batch' );
		$request->set_body_params( array( 'create' => array_fill( 0, 101, array( 'name' => 'Oversized batch' ) ) ) );
		$this->assertSame( 413, rest_do_request( $request )->get_status() );
	}
}
