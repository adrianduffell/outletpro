<?php
/**
 * Tests for add_classic_cart_badge_hook().
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
use const OutletPro\OUTLET_STATUS_TAXONOMY;

class Test_Add_Classic_Cart_Badge_Hook extends WP_UnitTestCase {
	public function test_appends_badge_to_classic_checkout_quantity(): void {
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
		$result = apply_filters( 'woocommerce_checkout_cart_item_quantity', '<strong>1</strong>', array( 'data' => $product ), 'cart-item-key' );

		// Assert.
		$this->assertStringStartsWith( '<strong>1</strong>', $result );
		$this->assertStringContainsString( 'outletpro-badge', $result );
		$this->assertTrue( wp_style_is( 'outletpro-classic-badge', 'enqueued' ) );
	}

	public function test_preserves_classic_checkout_quantity_for_non_outlet_product(): void {
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
		$result = apply_filters( 'woocommerce_checkout_cart_item_quantity', '<strong>1</strong>', array( 'data' => $product ), 'cart-item-key' );

		// Assert.
		$this->assertSame( '<strong>1</strong>', $result );
		$this->assertFalse( wp_style_is( 'outletpro-classic-badge', 'enqueued' ) );
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
		$result = apply_filters( 'woocommerce_checkout_cart_item_quantity', '', array( 'data' => $product ), 'cart-item-key' );

		// Assert.
		$this->assertSame( '<div class="outletpro-badge-container"><div class="outletpro-badge">Clearance &amp; more</div></div>', $result );
	}

	public function test_appends_badge_for_variation_of_outlet_product(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Clearance' );
		$product = WC_Helper_Product::create_variation_product();
		add_to_outlet( $product );
		$variation = wc_get_product( $product->get_children()[0] );
		deinit_cart();
		init_cart();

		// Act.
		$result = apply_filters( 'woocommerce_checkout_cart_item_quantity', '', array( 'data' => $variation ), 'cart-item-key' );

		// Assert.
		$this->assertStringContainsString( 'outletpro-badge', $result );
	}

	public function test_preserves_quantity_for_invalid_cart_item(): void {
		// Arrange.
		deinit_cart();
		init_cart();

		// Act.
		$result = apply_filters( 'woocommerce_checkout_cart_item_quantity', '<span>1</span>', array(), 'cart-item-key' );

		// Assert.
		$this->assertSame( '<span>1</span>', $result );
	}

	public function test_preserves_quantity_when_outlet_status_cannot_be_retrieved(): void {
		// Arrange.
		$product = WC_Helper_Product::create_simple_product();
		unregister_taxonomy( OUTLET_STATUS_TAXONOMY );
		deinit_cart();
		init_cart();

		// Act.
		$result = apply_filters( 'woocommerce_checkout_cart_item_quantity', '<span>1</span>', array( 'data' => $product ), 'cart-item-key' );
		register_outlet_status_taxonomy();

		// Assert.
		$this->assertSame( '<span>1</span>', $result );
	}

	public function test_appends_empty_badge_when_label_is_empty(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_BADGE_LABEL_OPTION, '' );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		deinit_cart();
		init_cart();

		// Act.
		$result = apply_filters( 'woocommerce_checkout_cart_item_quantity', '<span>1</span>', array( 'data' => $product ), 'cart-item-key' );

		// Assert.
		$this->assertSame( '<span>1</span><div class="outletpro-badge-container"><div class="outletpro-badge"></div></div>', $result );
	}

	public function test_appends_empty_badge_when_label_is_missing(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		delete_option( OUTLET_BADGE_LABEL_OPTION );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		deinit_cart();
		init_cart();

		// Act.
		$result = apply_filters( 'woocommerce_checkout_cart_item_quantity', '<span>1</span>', array( 'data' => $product ), 'cart-item-key' );

		// Assert.
		$this->assertSame( '<span>1</span><div class="outletpro-badge-container"><div class="outletpro-badge"></div></div>', $result );
	}
}
