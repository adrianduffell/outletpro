<?php
/**
 * Tests for validate_outletpro_product_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\init_taxonomies;
use function OutletPro\is_outlet;

class Test_Validate_Outletpro_Product_Hook extends WP_UnitTestCase {
	public function test_invalid_is_outlet_does_not_prevent_other_batch_updates(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$invalid = WC_Helper_Product::create_simple_product();
		$valid   = WC_Helper_Product::create_simple_product();
		$request = new WP_REST_Request( 'POST', '/wc/v3/products/batch' );
		$request->set_body_params(
			array(
				'update' => array(
					array(
						'id'        => $invalid->get_id(),
						'name'      => 'Must not save',
						'outletpro' => array( 'is_outlet' => 'false' ),
					),
					array(
						'id'        => $valid->get_id(),
						'outletpro' => array( 'is_outlet' => true ),
					),
				),
			)
		);

		// Act.
		$data = rest_do_request( $request )->get_data();

		// Assert.
		$this->assertSame( 400, $data['update'][0]['error']['data']['status'] );
		$this->assertSame( $invalid->get_name(), wc_get_product( $invalid->get_id() )->get_name() );
		$this->assertFalse( is_outlet( $invalid ) );
		$this->assertSame( array( 'is_outlet' => true ), $data['update'][1]['outletpro'] );
		$this->assertTrue( is_outlet( $valid ) );
	}

	public function test_rejects_adding_trashed_product_to_outlet(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$trashed = WC_Helper_Product::create_simple_product();
		wp_trash_post( $trashed->get_id() );
		$request = new WP_REST_Request( 'POST', '/wc/v3/products/batch' );
		$request->set_body_params(
			array(
				'update' => array(
					array(
						'id'        => $trashed->get_id(),
						'outletpro' => array( 'is_outlet' => true ),
					),
				),
			)
		);

		// Act.
		$data = rest_do_request( $request )->get_data();

		// Assert.
		$this->assertSame( 'outletpro_invalid_product', $data['update'][0]['error']['code'] );
	}
}
