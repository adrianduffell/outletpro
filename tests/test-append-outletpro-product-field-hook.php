<?php
/**
 * Product membership response tests.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\deinit_taxonomies;
use function OutletPro\init_taxonomies;

class Test_Append_Outletpro_Product_Field_Hook extends WP_UnitTestCase {
	public function test_returns_membership_in_collections_and_single_product_contexts(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		// Act.
		$view = new WP_REST_Request( 'GET', '/wc/v3/products/' . $product->get_id() );
		$edit = new WP_REST_Request( 'GET', '/wc/v3/products/' . $product->get_id() );
		$edit->set_param( 'context', 'edit' );
		$contexts = array( rest_do_request( $view )->get_data()['outletpro'], rest_do_request( $edit )->get_data()['outletpro'] );
		$request  = new WP_REST_Request( 'GET', '/wc/v3/products' );
		$request->set_query_params(
			array(
				'include'   => array( $product->get_id() ),
				'outletpro' => true,
			)
		);
		// Assert.
		$this->assertSame( array( array( 'is_outlet' => true ), array( 'is_outlet' => true ) ), $contexts );
		$this->assertSame( array( 'is_outlet' => true ), rest_do_request( $request )->get_data()[0]['outletpro'] );
	}

	public function test_respects_nested_field_selection_and_other_api_boundaries(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$product = WC_Helper_Product::create_simple_product();
		$request = new WP_REST_Request( 'GET', '/wc/v3/products/' . $product->get_id() );
		$request->set_query_params( array( '_fields' => 'outletpro.is_outlet' ) );
		// Act.
		$response = rest_filter_response_fields( rest_do_request( $request ), rest_get_server(), $request );
		// Assert.
		$this->assertSame( array( 'outletpro' => array( 'is_outlet' => false ) ), $response->get_data() );
		$responses = array_map( static fn( string $route ): WP_REST_Response => rest_do_request( $route . $product->get_id() ), array( '/wc/v1/products/', '/wc/v2/products/', '/wp/v2/product/', '/wc/store/v1/products/' ) );
		$this->assertSame( array( 200, 200, 200, 200 ), array_map( static fn( WP_REST_Response $result ): int => $result->get_status(), $responses ) );
		$this->assertSame( array( false, false, false, false ), array_map( static fn( WP_REST_Response $result ): bool => array_key_exists( 'outletpro', $result->get_data() ), $responses ) );
	}

	public function test_propagates_membership_read_failures(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Restore taxonomy registration after the failure.
		// Arrange.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$product = WC_Helper_Product::create_simple_product();
		deinit_taxonomies();
		// Expect.
		$this->expectException( RuntimeException::class );
		try {
			// Act.
			rest_do_request( new WP_REST_Request( 'GET', '/wc/v3/products/' . $product->get_id() ) );
		} finally {
			init_taxonomies();
		}
	}
}
