<?php
/**
 * Tests for validate_rest_field_callback().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\init_taxonomies;
use function OutletPro\validate_rest_field_callback;

class Test_Validate_Rest_Field_Callback extends WP_UnitTestCase {
	// phpcs:ignore Generic.Commenting.DocComment.MissingShort -- PHPUnit data provider metadata.
	/**
	 * @dataProvider field_values
	 * @param array<mixed> $value Field value.
	 */
	public function test_validates_optional_is_outlet_as_boolean( array $value, bool $expected ): void {
		// Arrange / Act.
		$valid = validate_rest_field_callback( $value );

		// Assert.
		$this->assertSame( $expected, $valid );
	}

	public function test_rejects_scalar_field_value(): void {
		// Arrange.
		$value = false;

		// Act.
		$valid = validate_rest_field_callback( $value );

		// Assert.
		$this->assertFalse( $valid );
	}

	public function test_rejects_non_boolean_is_outlet_through_rest(): void {
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

	/**
	 * Supply valid objects and invalid is_outlet values.
	 *
	 * @return array<string, array{array<mixed>, bool}> Field values and validation results.
	 */
	public static function field_values(): array {
		return array(
			'omitted' => array( array(), true ),
			'true'    => array( array( 'is_outlet' => true ), true ),
			'false'   => array( array( 'is_outlet' => false ), true ),
			'string'  => array( array( 'is_outlet' => 'false' ), false ),
			'number'  => array( array( 'is_outlet' => 0 ), false ),
			'null'    => array( array( 'is_outlet' => null ), false ),
			'list'    => array( array( true ), false ),
		);
	}
}
