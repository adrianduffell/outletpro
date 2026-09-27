<?php
/**
 * Tests for display_cart_item_outlet_badge_hook().
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
use const OutletPro\OUTLET_BADGE_LABEL_OPTION;

class Test_Display_Cart_Item_Outlet_Badge_Hook extends WP_UnitTestCase {
	public function test_displays_badge_for_outlet_product(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Clearance' );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		wp_register_style( 'outletpro-classic-badge', false, array(), 'test' );
		wp_dequeue_style( 'outletpro-classic-badge' );
		deinit_cart();
		init_cart();

		// Act.
		$result = apply_filters( 'woocommerce_cart_item_name', '<a>Product</a>', array( 'data' => $product ), 'cart-item-key' );

		// Assert.
		$this->assertSame( '<div class="outletpro-badge-container"><div class="outletpro-badge">Clearance</div></div><a>Product</a>', $result );
		$this->assertTrue( wp_style_is( 'outletpro-classic-badge', 'enqueued' ) );
	}

	public function test_escapes_badge_label(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Clearance & more' );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		deinit_cart();
		init_cart();

		// Act.
		$result = apply_filters( 'woocommerce_cart_item_name', '<a>Product</a>', array( 'data' => $product ), 'cart-item-key' );

		// Assert.
		$this->assertSame( '<div class="outletpro-badge-container"><div class="outletpro-badge">Clearance &amp; more</div></div><a>Product</a>', $result );
	}

	public function test_displays_nothing_for_non_outlet_product(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Clearance' );
		$product = WC_Helper_Product::create_simple_product();
		wp_register_style( 'outletpro-classic-badge', false, array(), 'test' );
		wp_dequeue_style( 'outletpro-classic-badge' );
		deinit_cart();
		init_cart();

		// Act.
		$result = apply_filters( 'woocommerce_cart_item_name', '<a>Product</a>', array( 'data' => $product ), 'cart-item-key' );

		// Assert.
		$this->assertSame( '<a>Product</a>', $result );
		$this->assertFalse( wp_style_is( 'outletpro-classic-badge', 'enqueued' ) );
	}

	public function test_displays_nothing_when_product_is_missing(): void {
		// Arrange.
		deinit_cart();
		init_cart();

		// Act.
		$result = apply_filters( 'woocommerce_cart_item_name', '<a>Product</a>', array(), 'cart-item-key' );

		// Assert.
		$this->assertSame( '<a>Product</a>', $result );
	}

	public function test_displays_empty_badge_when_label_is_empty(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_BADGE_LABEL_OPTION, '' );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		deinit_cart();
		init_cart();

		// Act.
		$result = apply_filters( 'woocommerce_cart_item_name', '<a>Product</a>', array( 'data' => $product ), 'cart-item-key' );

		// Assert.
		$this->assertSame( '<div class="outletpro-badge-container"><div class="outletpro-badge"></div></div><a>Product</a>', $result );
	}

	public function test_displays_empty_badge_when_label_is_missing(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		delete_option( OUTLET_BADGE_LABEL_OPTION );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		deinit_cart();
		init_cart();

		// Act.
		$result = apply_filters( 'woocommerce_cart_item_name', '<a>Product</a>', array( 'data' => $product ), 'cart-item-key' );

		// Assert.
		$this->assertSame( '<div class="outletpro-badge-container"><div class="outletpro-badge"></div></div><a>Product</a>', $result );
	}
}
