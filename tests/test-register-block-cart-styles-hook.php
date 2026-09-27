<?php
/**
 * Tests for register_block_cart_styles_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_enqueue;
use function OutletPro\enqueue_init;

class Test_Register_Block_Cart_Styles_Hook extends WP_UnitTestCase {
	public function test_registers_block_cart_styles_without_enqueuing_them(): void {
		// Arrange.
		deinit_enqueue();
		enqueue_init();

		// Act.
		do_action( 'wp_enqueue_scripts' );

		// Assert.
		$this->assertTrue( wp_style_is( 'outletpro-block-cart', 'registered' ) );
		$this->assertFalse( wp_style_is( 'outletpro-block-cart', 'enqueued' ) );
	}
}
