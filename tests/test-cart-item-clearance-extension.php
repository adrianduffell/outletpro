<?php
/**
 * Tests for the WooCommerce Store API cart item clearance extension callbacks.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_to_clearance;
use function WC_Clearance\cart_item_clearance_data_callback;
use function WC_Clearance\cart_item_clearance_schema_callback;
use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\seed_clearance_status_taxonomy;

class Test_Cart_Item_Clearance_Extension extends WP_UnitTestCase {

	public function test_data_callback_returns_false_when_product_does_not_exist(): void {
		// Arrange.
		$cart_item = array(
			'product_id'   => 99999,
			'variation_id' => 0,
		);

		// Act.
		$result = cart_item_clearance_data_callback( $cart_item );

		// Assert.
		$this->assertSame( array( 'is_clearance' => false ), $result );
	}

	public function test_data_callback_returns_false_for_non_clearance_product(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product   = \WC_Helper_Product::create_simple_product();
		$cart_item = array(
			'product_id'   => $product->get_id(),
			'variation_id' => 0,
		);

		// Act.
		$result = cart_item_clearance_data_callback( $cart_item );

		// Assert.
		$this->assertSame( array( 'is_clearance' => false ), $result );
	}

	public function test_data_callback_returns_true_for_clearance_product(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		$cart_item = array(
			'product_id'   => $product->get_id(),
			'variation_id' => 0,
		);

		// Act.
		$result = cart_item_clearance_data_callback( $cart_item );

		// Assert.
		$this->assertSame( array( 'is_clearance' => true ), $result );
	}

	public function test_data_callback_uses_variation_id_when_present(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$variable  = \WC_Helper_Product::create_variation_product();
		$variation = wc_get_product( current( $variable->get_children() ) );
		add_to_clearance( $variation );
		$cart_item = array(
			'product_id'   => $variable->get_id(),
			'variation_id' => $variation->get_id(),
		);

		// Act.
		$result = cart_item_clearance_data_callback( $cart_item );

		// Assert.
		$this->assertSame( array( 'is_clearance' => true ), $result );
	}

	public function test_schema_callback_returns_is_clearance_boolean_field(): void {
		// Act.
		$schema = cart_item_clearance_schema_callback();

		// Assert.
		$this->assertArrayHasKey( 'is_clearance', $schema );
		$this->assertSame( 'boolean', $schema['is_clearance']['type'] );
		$this->assertTrue( $schema['is_clearance']['readonly'] );
	}
}
