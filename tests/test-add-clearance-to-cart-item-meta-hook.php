<?php
/**
 * Tests for add_clearance_to_cart_item_meta_hook().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_to_clearance;
use function WC_Clearance\deinit_cart;
use function WC_Clearance\init_cart;
use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\seed_clearance_status_taxonomy;
use const WC_Clearance\CLEARANCE_BADGE_BG_COLOR_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_LABEL_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_TEXT_COLOR_OPTION;

class Test_Add_Clearance_To_Cart_Item_Meta_Hook extends WP_UnitTestCase {

	public function test_adds_clearance_meta_for_clearance_product(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		$cart_item = array( 'data' => $product );
		deinit_cart();
		init_cart();

		// Act.
		$result = apply_filters( 'woocommerce_get_item_data', array(), $cart_item );

		// Assert.
		$this->assertCount( 1, $result );
		$this->assertSame( 'Clearance', $result[0]['key'] );
		$this->assertSame( 'Yes', $result[0]['value'] );
	}

	public function test_does_not_add_clearance_meta_for_non_clearance_product(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product   = WC_Helper_Product::create_simple_product();
		$cart_item = array( 'data' => $product );
		deinit_cart();
		init_cart();

		// Act.
		$result = apply_filters( 'woocommerce_get_item_data', array(), $cart_item );

		// Assert.
		$this->assertCount( 0, $result );
	}

	public function test_uses_badge_label_setting_as_key(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		update_option( CLEARANCE_BADGE_LABEL_OPTION, 'On Sale' );
		$product = WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		$cart_item = array( 'data' => $product );
		deinit_cart();
		init_cart();

		// Act.
		$result = apply_filters( 'woocommerce_get_item_data', array(), $cart_item );

		// Assert.
		$this->assertSame( 'On Sale', $result[0]['key'] );
	}

	public function test_preserves_existing_item_data(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		$existing  = array(
			array(
				'key'   => 'Color',
				'value' => 'Red',
			),
		);
		$cart_item = array( 'data' => $product );
		deinit_cart();
		init_cart();

		// Act.
		$result = apply_filters( 'woocommerce_get_item_data', $existing, $cart_item );

		// Assert.
		$this->assertCount( 2, $result );
		$this->assertSame( 'Color', $result[0]['key'] );
		$this->assertSame( 'Clearance', $result[1]['key'] );
	}

	public function test_returns_item_data_unchanged_when_product_is_missing(): void {
		// Arrange.
		$cart_item = array();
		deinit_cart();
		init_cart();

		// Act.
		$result = apply_filters( 'woocommerce_get_item_data', array(), $cart_item );

		// Assert.
		$this->assertCount( 0, $result );
	}

	public function test_enqueues_cart_style_for_clearance_product(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product   = WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		$cart_item = array( 'data' => $product );
		deinit_cart();
		init_cart();
		wp_deregister_style( 'wc-clearance-cart' );
		wp_register_style( 'wc-clearance-cart', false );

		// Act.
		apply_filters( 'woocommerce_get_item_data', array(), $cart_item );

		// Assert.
		$this->assertTrue( wp_style_is( 'wc-clearance-cart', 'enqueued' ) );
	}

	public function test_does_not_enqueue_cart_style_for_non_clearance_product(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product   = WC_Helper_Product::create_simple_product();
		$cart_item = array( 'data' => $product );
		deinit_cart();
		init_cart();
		wp_deregister_style( 'wc-clearance-cart' );
		wp_register_style( 'wc-clearance-cart', false );

		// Act.
		apply_filters( 'woocommerce_get_item_data', array(), $cart_item );

		// Assert.
		$this->assertFalse( wp_style_is( 'wc-clearance-cart', 'enqueued' ) );
	}

	public function test_cart_style_inline_css_includes_bg_color_from_settings(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		update_option( CLEARANCE_BADGE_BG_COLOR_OPTION, '#FF0000' );
		$product   = WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		$cart_item = array( 'data' => $product );
		deinit_cart();
		init_cart();
		wp_deregister_style( 'wc-clearance-cart' );
		wp_register_style( 'wc-clearance-cart', false );

		// Act.
		apply_filters( 'woocommerce_get_item_data', array(), $cart_item );

		// Assert.
		$after = wp_styles()->get_data( 'wc-clearance-cart', 'after' );
		$this->assertStringContainsString( '--wc-clearance-badge-bg-color: #FF0000', implode( '', (array) $after ) );
	}

	public function test_cart_style_inline_css_includes_text_color_from_settings(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		update_option( CLEARANCE_BADGE_TEXT_COLOR_OPTION, '#00FF00' );
		$product   = WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		$cart_item = array( 'data' => $product );
		deinit_cart();
		init_cart();
		wp_deregister_style( 'wc-clearance-cart' );
		wp_register_style( 'wc-clearance-cart', false );

		// Act.
		apply_filters( 'woocommerce_get_item_data', array(), $cart_item );

		// Assert.
		$after = wp_styles()->get_data( 'wc-clearance-cart', 'after' );
		$this->assertStringContainsString( '--wc-clearance-badge-text-color: #00FF00', implode( '', (array) $after ) );
	}
}
