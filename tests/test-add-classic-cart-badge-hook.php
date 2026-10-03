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
use function OutletPro\deinit_taxonomies;
use function OutletPro\init_cart;
use function OutletPro\init_taxonomies;
use function OutletPro\seed_outlet_status_taxonomy;
use const OutletPro\OUTLET_BADGE_LABEL_OPTION;

class Test_Add_Classic_Cart_Badge_Hook extends WP_UnitTestCase {
	public function test_prepends_badge_to_classic_product_name(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Clearance' );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		wp_register_style( 'outletpro-classic-badge', false, array(), 'test' );
		wp_dequeue_style( 'outletpro-classic-badge' );
		add_filter( 'woocommerce_is_checkout', '__return_true' );
		deinit_cart();
		init_cart();

		// Act.
		$result = apply_filters( 'woocommerce_cart_item_name', '<strong>Product</strong>', array( 'data' => $product ), 'cart-item-key' );

		// Assert.
		$this->assertStringStartsWith( '<div class="outletpro-badge-container">', $result );
		$this->assertStringContainsString( 'outletpro-badge', $result );
		$this->assertStringEndsWith( '<strong>Product</strong>', $result );
		$this->assertTrue( wp_style_is( 'outletpro-classic-badge', 'enqueued' ) );
	}

	public function test_preserves_classic_product_name_for_non_outlet_product(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Clearance' );
		$product = WC_Helper_Product::create_simple_product();
		wp_register_style( 'outletpro-classic-badge', false, array(), 'test' );
		wp_dequeue_style( 'outletpro-classic-badge' );
		add_filter( 'woocommerce_is_checkout', '__return_true' );
		deinit_cart();
		init_cart();

		// Act.
		$result = apply_filters( 'woocommerce_cart_item_name', '<strong>Product</strong>', array( 'data' => $product ), 'cart-item-key' );

		// Assert.
		$this->assertSame( '<strong>Product</strong>', $result );
		$this->assertFalse( wp_style_is( 'outletpro-classic-badge', 'enqueued' ) );
	}

	public function test_escapes_badge_label(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Clearance & more' );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		add_filter( 'woocommerce_is_checkout', '__return_true' );
		deinit_cart();
		init_cart();

		// Act.
		$result = apply_filters( 'woocommerce_cart_item_name', '', array( 'data' => $product ), 'cart-item-key' );

		// Assert.
		$this->assertSame( '<div class="outletpro-badge-container"><div class="outletpro-badge">Clearance &amp; more</div></div>', $result );
	}

	public function test_prepends_badge_for_variation_of_outlet_product(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Clearance' );
		$product = WC_Helper_Product::create_variation_product();
		add_to_outlet( $product );
		$variation = wc_get_product( $product->get_children()[0] );
		add_filter( 'woocommerce_is_checkout', '__return_true' );
		deinit_cart();
		init_cart();

		// Act.
		$result = apply_filters( 'woocommerce_cart_item_name', '<span>Variation</span>', array( 'data' => $variation ), 'cart-item-key' );

		// Assert.
		$this->assertStringContainsString( 'outletpro-badge', $result );
	}

	public function test_preserves_product_name_for_invalid_cart_item(): void {
		// Arrange.
		add_filter( 'woocommerce_is_checkout', '__return_true' );
		deinit_cart();
		init_cart();

		// Act.
		$result = apply_filters( 'woocommerce_cart_item_name', '<span>Product</span>', array(), 'cart-item-key' );

		// Assert.
		$this->assertSame( '<span>Product</span>', $result );
	}

	public function test_preserves_product_name_when_outlet_status_cannot_be_retrieved(): void {
		// Arrange.
		$product = WC_Helper_Product::create_simple_product();
		deinit_taxonomies();
		add_filter( 'woocommerce_is_checkout', '__return_true' );
		deinit_cart();
		init_cart();

		// Act.
		$result = apply_filters( 'woocommerce_cart_item_name', '<span>Product</span>', array( 'data' => $product ), 'cart-item-key' );

		// Assert.
		$this->assertSame( '<span>Product</span>', $result );
	}

	/**
	 * Test empty badge label values.
	 *
	 * @dataProvider empty_badge_label_provider
	 */
	public function test_prepends_empty_badge_when_label_is_empty_or_missing( ?string $label ): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_BADGE_LABEL_OPTION, $label );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		add_filter( 'woocommerce_is_checkout', '__return_true' );
		deinit_cart();
		init_cart();

		// Act.
		$result = apply_filters( 'woocommerce_cart_item_name', '<span>Product</span>', array( 'data' => $product ), 'cart-item-key' );

		// Assert.
		$this->assertSame( '<div class="outletpro-badge-container"><div class="outletpro-badge"></div></div><span>Product</span>', $result );
	}

	/**
	 * Provide empty badge label values.
	 *
	 * @return array<string, array{?string}>
	 */
	public static function empty_badge_label_provider(): array {
		return array(
			'empty'   => array( '' ),
			'missing' => array( null ),
		);
	}
}
