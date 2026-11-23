<?php
/**
 * Tests for validate_outletpro_product_field().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\init_taxonomies;
use function OutletPro\validate_outletpro_product_field;

class Test_Validate_Outletpro_Product_Field extends WP_UnitTestCase {
	public function test_validates_optional_is_outlet_as_boolean(): void {
		// Arrange.
		$valid   = array( array(), array( 'is_outlet' => true ), array( 'is_outlet' => false ) );
		$invalid = array( null, true, 'false', 0, array( true ), array( 'is_outlet' => null ), array( 'is_outlet' => 'false' ), array( 'is_outlet' => 0 ), array( 'is_outlet' => 1 ) );

		// Act.
		$accepted = array_map( 'OutletPro\validate_outletpro_product_field', $valid );
		$rejected = array_map( 'OutletPro\validate_outletpro_product_field', $invalid );

		// Assert.
		$this->assertFalse( validate_outletpro_product_field( 'true' ) );
		$this->assertSame( array_fill( 0, count( $valid ), true ), $accepted );
		$this->assertSame( array_fill( 0, count( $invalid ), false ), $rejected );
	}

	public function test_rejects_non_boolean_is_outlet_before_saving_product(): void {
		// Arrange.
		init_taxonomies();
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
