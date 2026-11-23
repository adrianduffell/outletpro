<?php
/**
 * Tests for append_outletpro_product_field_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\deinit_taxonomies;
use function OutletPro\init_taxonomies;

class Test_Append_Outletpro_Product_Field_Hook extends WP_UnitTestCase {
	public function test_is_outlet_true_for_outlet_product_in_collection(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$request = new WP_REST_Request( 'GET', '/wc/v3/products' );
		$request->set_query_params(
			array(
				'include'   => array( $product->get_id() ),
				'outletpro' => true,
			)
		);

		// Act.
		$response = rest_do_request( $request );

		// Assert.
		$this->assertSame( array( 'is_outlet' => true ), $response->get_data()[0]['outletpro'] );
	}

	// phpcs:ignore Generic.Commenting.DocComment.MissingShort -- PHPUnit data provider metadata.
	/**
	 * @dataProvider product_contexts
	 */
	public function test_is_outlet_true_for_outlet_product_in_context( string $context ): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$request = new WP_REST_Request( 'GET', '/wc/v3/products/' . $product->get_id() );
		$request->set_param( 'context', $context );

		// Act.
		$response = rest_do_request( $request );

		// Assert.
		$this->assertSame( array( 'is_outlet' => true ), $response->get_data()['outletpro'] );
	}

	public function test_includes_is_outlet_when_requested(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$product = WC_Helper_Product::create_simple_product();
		$request = new WP_REST_Request( 'GET', '/wc/v3/products/' . $product->get_id() );
		$request->set_query_params( array( '_fields' => 'outletpro.is_outlet' ) );

		// Act.
		$response = rest_do_request( $request );

		// Assert.
		$this->assertSame( array( 'is_outlet' => false ), $response->get_data()['outletpro'] );
	}

	public function test_omits_is_outlet_when_not_requested(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$product = WC_Helper_Product::create_simple_product();
		$request = new WP_REST_Request( 'GET', '/wc/v3/products/' . $product->get_id() );
		$request->set_param( '_fields', 'id' );

		// Act.
		$response = rest_do_request( $request );

		// Assert.
		$this->assertArrayNotHasKey( 'outletpro', $response->get_data() );
	}

	public function test_omits_is_outlet_when_unavailable_for_single_product(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$product = WC_Helper_Product::create_simple_product();
		deinit_taxonomies();

		// Act.
		$response = rest_do_request( new WP_REST_Request( 'GET', '/wc/v3/products/' . $product->get_id() ) );
		init_taxonomies();

		// Assert.
		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( $product->get_name(), $response->get_data()['name'] );
		$this->assertArrayNotHasKey( 'outletpro', $response->get_data() );
	}

	public function test_omits_is_outlet_when_unavailable_in_product_collection(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$product = WC_Helper_Product::create_simple_product();
		$request = new WP_REST_Request( 'GET', '/wc/v3/products' );
		$request->set_param( 'include', array( $product->get_id() ) );
		deinit_taxonomies();

		// Act.
		$response = rest_do_request( $request );
		init_taxonomies();

		// Assert.
		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( $product->get_id(), $response->get_data()[0]['id'] );
		$this->assertArrayNotHasKey( 'outletpro', $response->get_data()[0] );
	}

	/**
	 * Supply product REST contexts.
	 *
	 * @return array<string, array{string}> Named contexts.
	 */
	public static function product_contexts(): array {
		return array(
			'view' => array( 'view' ),
			'edit' => array( 'edit' ),
		);
	}
}
