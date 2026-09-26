<?php
/**
 * Tests for add_outlet_to_cart_item_class_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\deinit_cart;
use function OutletPro\init_cart;
use function OutletPro\register_outlet_status_taxonomy;
use function OutletPro\seed_outlet_status_taxonomy;

class Test_Add_Outlet_To_Cart_Item_Class_Hook extends WP_UnitTestCase {
	public function test_init_registers_cart_item_class_filter_at_default_priority(): void {
		// Arrange.
		deinit_cart();

		// Act.
		init_cart();

		// Assert.
		$this->assertSame( 10, has_filter( 'woocommerce_cart_item_class', 'OutletPro\add_outlet_to_cart_item_class_hook' ) );
	}

	public function test_adds_outlet_class_for_outlet_product(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$cart_item = array( 'data' => $product );
		deinit_cart();
		init_cart();

		// Act.
		$result = apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, 'key' );

		// Assert.
		$this->assertSame( 'cart_item outletpro-cart-item', $result );
	}

	public function test_adds_outlet_class_without_leading_space_when_existing_classes_are_empty(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$cart_item = array( 'data' => $product );
		deinit_cart();
		init_cart();

		// Act.
		$result = apply_filters( 'woocommerce_cart_item_class', '', $cart_item, 'key' );

		// Assert.
		$this->assertSame( 'outletpro-cart-item', $result );
	}

	public function test_preserves_existing_classes(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$cart_item = array( 'data' => $product );
		deinit_cart();
		init_cart();

		// Act.
		$result = apply_filters( 'woocommerce_cart_item_class', 'cart_item custom', $cart_item, 'key' );

		// Assert.
		$this->assertSame( 'cart_item custom outletpro-cart-item', $result );
	}

	public function test_does_not_add_outlet_class_for_non_outlet_product(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product   = WC_Helper_Product::create_simple_product();
		$cart_item = array( 'data' => $product );
		deinit_cart();
		init_cart();

		// Act.
		$result = apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, 'key' );

		// Assert.
		$this->assertSame( 'cart_item', $result );
	}

	public function test_does_not_add_outlet_class_when_product_is_missing(): void {
		// Arrange.
		deinit_cart();
		init_cart();

		// Act.
		$result = apply_filters( 'woocommerce_cart_item_class', 'cart_item', array(), 'key' );

		// Assert.
		$this->assertSame( 'cart_item', $result );
	}
}
