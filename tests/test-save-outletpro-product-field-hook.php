<?php
/**
 * Product membership persistence tests.
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
	public function test_creates_outlet_products_through_single_and_batch_requests(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'shop_manager' ) ) );
		$request = new WP_REST_Request( 'POST', '/wc/v3/products' );
		$request->set_body_params(
			array(
				'name'      => 'Outlet creation',
				'outletpro' => array( 'is_outlet' => true ),
			)
		);
		$batch = new WP_REST_Request( 'POST', '/wc/v3/products/batch' );
		$batch->set_body_params(
			array(
				'create' => array(
					array(
						'name'      => 'Batch creation',
						'outletpro' => array( 'is_outlet' => false ),
					),
					array(
						'name'      => 'Outlet batch creation',
						'outletpro' => array( 'is_outlet' => true ),
					),
					array(
						'name'      => 'Invalid batch creation',
						'outletpro' => array( 'is_outlet' => 'true' ),
					),
				),
			)
		);
		// Act.
		$response = rest_do_request( $request );
		$data     = rest_do_request( $batch )->get_data();
		// Assert.
		$this->assertSame( 201, $response->get_status() );
		$this->assertTrue( is_outlet( wc_get_product( $response->get_data()['id'] ) ) );
		$this->assertSame( array( 'is_outlet' => false ), $data['create'][0]['outletpro'] );
		$this->assertTrue( is_outlet( wc_get_product( $data['create'][1]['id'] ) ) );
		$this->assertSame( 400, $data['create'][2]['error']['data']['status'] );
	}

	public function test_updates_idempotently_and_preserves_omitted_membership(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$product = WC_Helper_Product::create_simple_product();
		$request = new WP_REST_Request( 'PUT', '/wc/v3/products/' . $product->get_id() );
		$before  = did_action( 'outletpro_status_changed' );
		// Act.
		$request->set_body_params( array( 'outletpro' => array( 'is_outlet' => true ) ) );
		$first    = rest_do_request( $request );
		$repeated = rest_do_request( $request );
		$request->set_body_params( array( 'outletpro' => array() ) );
		$empty = rest_do_request( $request );
		$request->set_body_params( array( 'name' => 'Membership preserved' ) );
		$omitted = rest_do_request( $request );
		// Assert.
		$this->assertSame( array( 200, 200, 200, 200 ), array( $first->get_status(), $repeated->get_status(), $empty->get_status(), $omitted->get_status() ) );
		$this->assertTrue( is_outlet( $product ) );
		$request->set_body_params( array( 'outletpro' => array( 'is_outlet' => false ) ) );
		$this->assertSame( 200, rest_do_request( $request )->get_status() );
		$this->assertFalse( is_outlet( $product ) );
		$this->assertSame( $before + 2, did_action( 'outletpro_status_changed' ) );
	}

	public function test_reports_membership_storage_failures_per_batch_item(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Restore taxonomy registration after the failure.
		// Arrange.
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
		try {
			// Act.
			$data = rest_do_request( $request )->get_data();
			// Assert.
			$this->assertSame( 'outletpro_membership_failed', $data['update'][0]['error']['code'] );
			$this->assertSame( 500, $data['update'][0]['error']['data']['status'] );
		} finally {
			init_taxonomies();
		}
	}

	public function test_batches_mixed_membership_changes_with_per_product_permissions(): void {
		// Arrange.
		init_taxonomies();
		$remove = WC_Helper_Product::create_simple_product();
		$add    = WC_Helper_Product::create_simple_product();
		$denied = WC_Helper_Product::create_simple_product();
		$denied->set_status( 'private' );
		$denied->save();
		wp_update_post(
			array(
				'ID'          => $denied->get_id(),
				'post_author' => $this->factory->user->create(),
			)
		);
		add_to_outlet( $remove );
		$user = new WP_User( $this->factory->user->create( array( 'role' => 'subscriber' ) ) );
		$user->add_cap( 'edit_products' );
		$user->add_cap( 'edit_others_products' );
		$user->add_cap( 'edit_published_products' );
		wp_set_current_user( $user->ID );
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
					array(
						'id'        => $denied->get_id(),
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
		$this->assertFalse( is_outlet( $denied ) );
		$this->assertSame( false, $data['update'][0]['outletpro']['is_outlet'] );
		$this->assertSame( true, $data['update'][1]['outletpro']['is_outlet'] );
		$this->assertSame( 403, $data['update'][2]['error']['data']['status'] );
	}
}
