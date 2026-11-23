<?php
/**
 * Tests for update_rest_field_callback().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\deinit_taxonomies;
use function OutletPro\init_taxonomies;
use function OutletPro\is_outlet;
use function OutletPro\set_outlet;

class Test_Update_Rest_Field_Callback extends WP_UnitTestCase {
	// phpcs:ignore Generic.Commenting.DocComment.MissingShort -- PHPUnit data provider metadata.
	/**
	 * @dataProvider product_updates
	 */
	public function test_updates_is_outlet_for_product( string $route, bool $outlet ): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$product = WC_Helper_Product::create_simple_product();
		set_outlet( $product, ! $outlet );
		$request = new WP_REST_Request( 'PUT', $route . '/' . $product->get_id() );
		$request->set_body_params( array( 'outletpro' => array( 'is_outlet' => $outlet ) ) );

		// Act.
		$response = rest_do_request( $request );

		// Assert.
		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( $outlet, is_outlet( $product ) );
	}

	// phpcs:ignore Generic.Commenting.DocComment.MissingShort -- PHPUnit data provider metadata.
	/**
	 * @dataProvider product_routes
	 */
	public function test_creates_outlet_product( string $route ): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$request = new WP_REST_Request( 'POST', $route );
		$request->set_body_params(
			array(
				'title'     => 'Outlet product',
				'name'      => 'Outlet product',
				'outletpro' => array( 'is_outlet' => true ),
			)
		);

		// Act.
		$response = rest_do_request( $request );

		// Assert.
		$this->assertSame( 201, $response->get_status() );
		$this->assertTrue( is_outlet( wc_get_product( $response->get_data()['id'] ) ) );
	}

	public function test_adds_and_removes_outlet_products_in_batch(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$remove = WC_Helper_Product::create_simple_product();
		$add    = WC_Helper_Product::create_simple_product();
		add_to_outlet( $remove );
		$request = new WP_REST_Request( 'POST', '/wc/v3/products/batch' );
		$request->set_body_params(
			array(
				'update' => array(
					array(
						'id'        => $remove->get_id(),
						'outletpro' => array( 'is_outlet' => false ),
					),
					array(
						'id'        => $add->get_id(),
						'outletpro' => array( 'is_outlet' => true ),
					),
				),
			)
		);

		// Act.
		$data = rest_do_request( $request )->get_data();

		// Assert.
		$this->assertFalse( is_outlet( $remove ) );
		$this->assertTrue( is_outlet( $add ) );
		$this->assertSame( false, $data['update'][0]['outletpro']['is_outlet'] );
		$this->assertSame( true, $data['update'][1]['outletpro']['is_outlet'] );
	}

	public function test_product_remains_in_outlet_when_is_outlet_is_omitted(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$request = new WP_REST_Request( 'PUT', '/wc/v3/products/' . $product->get_id() );
		$request->set_body_params( array( 'outletpro' => array() ) );

		// Act.
		$response = rest_do_request( $request );

		// Assert.
		$this->assertSame( 200, $response->get_status() );
		$this->assertTrue( is_outlet( $product ) );
	}

	// phpcs:ignore Generic.Commenting.DocComment.MissingShort -- PHPUnit data provider metadata.
	/**
	 * @dataProvider product_routes
	 */
	public function test_returns_error_when_outlet_product_cannot_be_updated( string $route ): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$product = WC_Helper_Product::create_simple_product();
		$request = new WP_REST_Request( 'PUT', $route . '/' . $product->get_id() );
		$request->set_body_params( array( 'outletpro' => array( 'is_outlet' => true ) ) );
		deinit_taxonomies();

		// Act.
		$response = rest_do_request( $request );
		init_taxonomies();

		// Assert.
		$this->assertSame( 500, $response->get_status() );
		$this->assertSame( 'outletpro_update_failed', $response->get_data()['code'] );
	}

	/**
	 * Supply shared product API routes.
	 *
	 * @return array<string, array{string}> Product routes.
	 */
	public static function product_routes(): array {
		return array(
			'WooCommerce'    => array( '/wc/v3/products' ),
			'WooCommerce v1' => array( '/wc/v1/products' ),
			'WordPress'      => array( '/wp/v2/product' ),
		);
	}

	/**
	 * Supply additions and removals through both product APIs.
	 *
	 * @return array<string, array{string, bool}> Routes and requested outlet status.
	 */
	public static function product_updates(): array {
		return array(
			'WooCommerce add'    => array( '/wc/v3/products', true ),
			'WooCommerce remove' => array( '/wc/v3/products', false ),
			'WordPress add'      => array( '/wp/v2/product', true ),
			'WordPress remove'   => array( '/wp/v2/product', false ),
		);
	}
}
