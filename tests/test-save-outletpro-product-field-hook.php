<?php
/**
 * Tests for save_outletpro_product_field_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\deinit_taxonomies;
use function OutletPro\init_taxonomies;
use function OutletPro\is_outlet;

class Test_Save_Outletpro_Product_Field_Hook extends WP_UnitTestCase {
	public function test_creates_an_outlet_product(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$request = new WP_REST_Request( 'POST', '/wc/v3/products' );
		$request->set_body_params(
			array(
				'name'      => 'Outlet creation',
				'outletpro' => array( 'is_outlet' => true ),
			)
		);

		// Act.
		$response = rest_do_request( $request );

		// Assert.
		$this->assertSame( 201, $response->get_status() );
		$this->assertTrue( is_outlet( wc_get_product( $response->get_data()['id'] ) ) );
	}

	public function test_creates_outlet_products_in_a_batch(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$request = new WP_REST_Request( 'POST', '/wc/v3/products/batch' );
		$request->set_body_params(
			array(
				'create' => array(
					array(
						'name'      => 'Outlet batch creation',
						'outletpro' => array( 'is_outlet' => true ),
					),
				),
			)
		);

		// Act.
		$data = rest_do_request( $request )->get_data();

		// Assert.
		$this->assertTrue( is_outlet( wc_get_product( $data['create'][0]['id'] ) ) );
	}

	public function test_is_outlet_true_for_outlet_product(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$product = WC_Helper_Product::create_simple_product();
		$request = new WP_REST_Request( 'PUT', '/wc/v3/products/' . $product->get_id() );
		$request->set_body_params( array( 'outletpro' => array( 'is_outlet' => true ) ) );

		// Act.
		$response = rest_do_request( $request );

		// Assert.
		$this->assertSame( 200, $response->get_status() );
		$this->assertTrue( is_outlet( $product ) );
	}

	public function test_is_outlet_false_for_product_removed_from_outlet(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$request = new WP_REST_Request( 'PUT', '/wc/v3/products/' . $product->get_id() );
		$request->set_body_params( array( 'outletpro' => array( 'is_outlet' => false ) ) );

		// Act.
		$response = rest_do_request( $request );

		// Assert.
		$this->assertSame( 200, $response->get_status() );
		$this->assertFalse( is_outlet( $product ) );
	}

	public function test_product_remains_in_outlet_when_outletpro_is_omitted(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$request = new WP_REST_Request( 'PUT', '/wc/v3/products/' . $product->get_id() );
		$request->set_body_params( array( 'name' => 'Outlet product' ) );

		// Act.
		$response = rest_do_request( $request );

		// Assert.
		$this->assertSame( 200, $response->get_status() );
		$this->assertTrue( is_outlet( $product ) );
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

	public function test_reports_error_when_product_cannot_be_added_to_outlet_in_batch(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$product = WC_Helper_Product::create_simple_product();
		$request = new WP_REST_Request( 'POST', '/wc/v3/products/batch' );
		$request->set_body_params(
			array(
				'update' => array(
					array(
						'id'        => $product->get_id(),
						'outletpro' => array( 'is_outlet' => true ),
					),
				),
			)
		);
		deinit_taxonomies();

		// Act.
		$data = rest_do_request( $request )->get_data();
		init_taxonomies();

		// Assert.
		$this->assertSame( 'outletpro_membership_failed', $data['update'][0]['error']['code'] );
		$this->assertSame( 500, $data['update'][0]['error']['data']['status'] );
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
}
