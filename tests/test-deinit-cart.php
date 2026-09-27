<?php
/**
 * Tests for deinit_cart().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_cart;
use function OutletPro\init_cart;

class Test_Deinit_Cart extends WP_UnitTestCase {
	public function test_removes_block_render_hooks(): void {
		// Arrange.
		init_cart();

		// Act.
		deinit_cart();

		// Assert.
		$this->assertFalse( has_filter( 'render_block_woocommerce/cart', 'OutletPro\enqueue_block_cart_assets_hook' ) );
		$this->assertFalse( has_filter( 'render_block_woocommerce/checkout', 'OutletPro\enqueue_block_cart_assets_hook' ) );
		$this->assertFalse( has_filter( 'render_block_woocommerce/mini-cart', 'OutletPro\enqueue_block_cart_assets_hook' ) );
	}
}
