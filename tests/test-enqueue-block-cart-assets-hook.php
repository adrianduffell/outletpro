<?php
/**
 * Tests for enqueue_block_cart_assets_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_cart;
use function OutletPro\deinit_enqueue;
use function OutletPro\enqueue_init;
use function OutletPro\init_cart;

class Test_Enqueue_Block_Cart_Assets_Hook extends WP_UnitTestCase {
	public function test_enqueues_block_cart_assets_when_cart_block_is_rendered(): void {
		// Arrange.
		deinit_cart();
		deinit_enqueue();
		enqueue_init();
		init_cart();
		do_action( 'wp_enqueue_scripts' );

		// Act.
		$result = apply_filters( 'render_block_woocommerce/cart', '<div>Cart</div>' ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

		// Assert.
		$this->assertSame( '<div>Cart</div>', $result );
		$this->assertTrue( wp_style_is( 'outletpro-block-cart', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'outletpro-block-cart', 'enqueued' ) );
	}

	public function test_enqueues_block_cart_assets_when_checkout_block_is_rendered(): void {
		// Arrange.
		deinit_cart();
		deinit_enqueue();
		enqueue_init();
		init_cart();
		do_action( 'wp_enqueue_scripts' );

		// Act.
		$result = apply_filters( 'render_block_woocommerce/checkout', '<div>Checkout</div>' ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

		// Assert.
		$this->assertSame( '<div>Checkout</div>', $result );
		$this->assertTrue( wp_style_is( 'outletpro-block-cart', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'outletpro-block-cart', 'enqueued' ) );
	}

	public function test_enqueues_block_cart_assets_when_mini_cart_block_is_rendered(): void {
		// Arrange.
		deinit_cart();
		deinit_enqueue();
		enqueue_init();
		init_cart();
		do_action( 'wp_enqueue_scripts' );

		// Act.
		$result = apply_filters( 'render_block_woocommerce/mini-cart', '<div>Mini-cart</div>' ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

		// Assert.
		$this->assertSame( '<div>Mini-cart</div>', $result );
		$this->assertTrue( wp_style_is( 'outletpro-block-cart', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'outletpro-block-cart', 'enqueued' ) );
	}
}
