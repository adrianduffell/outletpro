<?php
/**
 * Tests for validate_rest_field_woocommerce_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\init_taxonomies;

class Test_Validate_Rest_Field_Woocommerce_Hook extends WP_UnitTestCase {
	// phpcs:ignore Generic.Commenting.DocComment.MissingShort -- PHPUnit data provider metadata.
	/**
	 * @dataProvider product_controllers
	 */
	public function test_rejects_invalid_is_outlet_before_product_is_saved( string $controller_class ): void {
		// Arrange.
		init_taxonomies();
		$product    = WC_Helper_Product::create_simple_product();
		$controller = new $controller_class();
		$request    = new WP_REST_Request( 'PUT', '/wc/v3/products/' . $product->get_id() );
		$request->set_body_params(
			array(
				'id'        => $product->get_id(),
				'name'      => 'Must not save',
				'outletpro' => array( 'is_outlet' => 'false' ),
			)
		);

		// Act.
		$result = $controller->update_item( $request );

		// Assert.
		$this->assertWPError( $result );
		$this->assertSame( 'outletpro_invalid_is_outlet', $result->get_error_code() );
		$this->assertSame( $product->get_name(), wc_get_product( $product->get_id() )->get_name() );
	}

	/**
	 * Supply WooCommerce product controllers with different error handling.
	 *
	 * @return array<string, array{string}> Controller classes.
	 */
	public static function product_controllers(): array {
		return array(
			'WooCommerce'    => array( WC_REST_Products_Controller::class ),
			'WooCommerce v1' => array( WC_REST_Products_V1_Controller::class ),
		);
	}
}
