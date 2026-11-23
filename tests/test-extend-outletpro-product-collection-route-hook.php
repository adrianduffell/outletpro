<?php
/**
 * Tests for extend_outletpro_product_collection_route_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\init_taxonomies;

class Test_Extend_Outletpro_Product_Collection_Route_Hook extends WP_UnitTestCase {
	public function test_product_collection_schema_exposes_boolean_is_outlet(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		add_filter( 'rest_pre_dispatch', 'rest_handle_options_request', 10, 3 );

		// Act.
		$products = rest_do_request( new WP_REST_Request( 'OPTIONS', '/wc/v3/products' ) )->get_data();

		// Assert.
		$this->assertSame( 'boolean', $products['schema']['properties']['outletpro']['properties']['is_outlet']['type'] );
	}
}
