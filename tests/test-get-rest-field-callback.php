<?php
/**
 * Tests for get_rest_field_callback().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\deinit_taxonomies;
use function OutletPro\init_taxonomies;

class Test_Get_Rest_Field_Callback extends WP_UnitTestCase {
	// phpcs:ignore Generic.Commenting.DocComment.MissingShort -- PHPUnit data provider metadata.
	/**
	 * @dataProvider product_contexts
	 */
	public function test_is_outlet_true_for_outlet_product( string $route, string $context ): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$request = new WP_REST_Request( 'GET', $route . $product->get_id() );
		$request->set_param( 'context', $context );

		// Act.
		$response = rest_do_request( $request );

		// Assert.
		$this->assertSame( array( 'is_outlet' => true ), $response->get_data()['outletpro'] );
	}

	public function test_is_outlet_false_for_product_not_in_outlet(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$product = WC_Helper_Product::create_simple_product();

		// Act.
		$response = rest_do_request( '/wc/v3/products/' . $product->get_id() );

		// Assert.
		$this->assertSame( array( 'is_outlet' => false ), $response->get_data()['outletpro'] );
	}

	public function test_omits_is_outlet_when_unavailable(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$product = WC_Helper_Product::create_simple_product();
		deinit_taxonomies();

		// Act.
		$response = rest_do_request( '/wc/v3/products/' . $product->get_id() );
		init_taxonomies();

		// Assert.
		$this->assertSame( 200, $response->get_status() );
		$this->assertArrayNotHasKey( 'is_outlet', $response->get_data()['outletpro'] );
	}

	/**
	 * Supply WordPress and WooCommerce product contexts.
	 *
	 * @return array<string, array{string, string}> Product routes and contexts.
	 */
	public static function product_contexts(): array {
		return array(
			'WooCommerce view' => array( '/wc/v3/products/', 'view' ),
			'WooCommerce edit' => array( '/wc/v3/products/', 'edit' ),
			'WordPress view'   => array( '/wp/v2/product/', 'view' ),
			'WordPress edit'   => array( '/wp/v2/product/', 'edit' ),
		);
	}
}
