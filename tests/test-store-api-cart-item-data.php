<?php
/**
 * Tests for store_api_cart_item_data().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\register_outlet_status_taxonomy;
use function OutletPro\seed_outlet_status_taxonomy;
use function OutletPro\store_api_cart_item_data;
use const OutletPro\OUTLET_STATUS_TAXONOMY;

class Test_Store_Api_Cart_Item_Data extends WP_UnitTestCase {

	public function test_returns_true_for_outlet_product(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );

		// Act.
		$result = store_api_cart_item_data( array( 'data' => $product ) );

		// Assert.
		$this->assertTrue( $result['is_outlet'] );
	}

	public function test_returns_false_for_non_outlet_product(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();

		// Act.
		$result = store_api_cart_item_data( array( 'data' => $product ) );

		// Assert.
		$this->assertFalse( $result['is_outlet'] );
	}

	public function test_returns_true_for_variation_of_outlet_product(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_variation_product();
		add_to_outlet( $product );
		$variation = wc_get_product( $product->get_children()[0] );

		// Act.
		$result = store_api_cart_item_data( array( 'data' => $variation ) );

		// Assert.
		$this->assertTrue( $result['is_outlet'] );
	}

	public function test_returns_empty_data_for_invalid_cart_item(): void {
		// Arrange.
		$cart_item = array();

		// Act.
		$result = store_api_cart_item_data( $cart_item );

		// Assert.
		$this->assertSame( array(), $result );
	}

	public function test_returns_empty_data_when_outlet_status_cannot_be_retrieved(): void {
		// Arrange.
		$product = WC_Helper_Product::create_simple_product();
		unregister_taxonomy( OUTLET_STATUS_TAXONOMY );

		// Act.
		$result = store_api_cart_item_data( array( 'data' => $product ) );
		register_outlet_status_taxonomy();

		// Assert.
		$this->assertSame( array(), $result );
	}
}
