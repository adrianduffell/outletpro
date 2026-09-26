<?php
/**
 * Tests for init_store_api().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CartItemSchema;
use Automattic\WooCommerce\StoreApi\StoreApi;
use function OutletPro\add_to_outlet;
use function OutletPro\init_store_api;
use function OutletPro\register_outlet_status_taxonomy;
use function OutletPro\seed_outlet_status_taxonomy;

class Test_Init_Store_Api extends WP_UnitTestCase {

	public function test_defines_boolean_schema(): void {
		// Arrange.
		init_store_api();
		$extend_schema = StoreApi::container()->get( ExtendSchema::class );

		// Act.
		$result = $extend_schema->get_endpoint_schema( CartItemSchema::IDENTIFIER );
		$field  = $result->outletpro['properties']['is_outlet'];

		// Assert.
		$this->assertSame( 'boolean', $field['type'] );
		$this->assertTrue( $field['readonly'] );
		$this->assertArrayNotHasKey( 'context', $field );
	}

	public function test_registers_data_under_outletpro_namespace(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		init_store_api();
		$extend_schema = StoreApi::container()->get( ExtendSchema::class );

		// Act.
		$result = $extend_schema->get_endpoint_data(
			CartItemSchema::IDENTIFIER,
			array( array( 'data' => $product ) )
		);

		// Assert.
		$this->assertTrue( $result->outletpro['is_outlet'] );
	}
}
