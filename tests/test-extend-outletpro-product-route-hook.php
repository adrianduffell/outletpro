<?php
/**
 * Tests for extend_outletpro_product_route_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\init_taxonomies;

class Test_Extend_Outletpro_Product_Route_Hook extends WP_UnitTestCase {
	public function test_product_schema_exposes_outletpro_object(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		add_filter( 'rest_pre_dispatch', 'rest_handle_options_request', 10, 3 );

		// Act.
		$data = rest_do_request( new WP_REST_Request( 'OPTIONS', '/wc/v3/products/123' ) )->get_data();

		// Assert.
		$this->assertSame( 'object', $data['schema']['properties']['outletpro']['type'] );
	}
}
