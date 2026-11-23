<?php
/**
 * WooCommerce route extension tests.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\init_taxonomies;

class Test_Extend_Outletpro_Product_Route_Hook extends WP_UnitTestCase {
	public function test_publishes_the_single_product_write_schema(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		add_filter( 'rest_pre_dispatch', 'rest_handle_options_request', 10, 3 );
		// Act.
		$data = rest_do_request( new WP_REST_Request( 'OPTIONS', '/wc/v3/products/123' ) )->get_data();
		// Assert.
		$this->assertSame( 'object', $data['schema']['properties']['outletpro']['type'] );
		$this->assertSame( 'object', $data['endpoints'][1]['args']['outletpro']['type'] );
	}

	public function test_rejects_invalid_single_writes_before_saving(): void {
		// Arrange.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$product = WC_Helper_Product::create_simple_product();
		$request = new WP_REST_Request( 'PUT', '/wc/v3/products/' . $product->get_id() );
		$request->set_body_params(
			array(
				'name'      => 'Must not save',
				'outletpro' => array( 'is_outlet' => 'false' ),
			)
		);
		// Act.
		$response = rest_do_request( $request );
		// Assert.
		$this->assertSame( 400, $response->get_status() );
		$this->assertSame( $product->get_name(), wc_get_product( $product->get_id() )->get_name() );
	}
}
