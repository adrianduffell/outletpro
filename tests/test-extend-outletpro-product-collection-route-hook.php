<?php
/**
 * WooCommerce route extension tests.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\init_taxonomies;

class Test_Extend_Outletpro_Product_Collection_Route_Hook extends WP_UnitTestCase {
	public function test_publishes_only_v3_product_and_batch_schemas(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		add_filter( 'rest_pre_dispatch', 'rest_handle_options_request', 10, 3 );
		// Act.
		$products = rest_do_request( new WP_REST_Request( 'OPTIONS', '/wc/v3/products' ) )->get_data();
		// Assert.
		$this->assertArrayHasKey( 'schema', $products, wp_json_encode( $products ) );
		$this->assertSame( 'boolean', $products['schema']['properties']['outletpro']['properties']['is_outlet']['type'] );
		$this->assertSame( 'boolean', $products['endpoints'][0]['args']['outletpro']['type'] );
		$this->assertSame( 'object', $products['endpoints'][1]['args']['outletpro']['type'] );
		$schemas = array_map( static fn( string $route ): array => rest_do_request( new WP_REST_Request( 'OPTIONS', $route ) )->get_data()['schema']['properties'], array( '/wc/v1/products', '/wc/v2/products', '/wp/v2/product', '/wc/store/v1/products' ) );
		$this->assertSame( array( false, false, false, false ), array_map( static fn( array $schema ): bool => array_key_exists( 'outletpro', $schema ), $schemas ) );
	}
}
