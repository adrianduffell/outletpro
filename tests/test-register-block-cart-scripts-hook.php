<?php
/**
 * Tests for register_block_cart_scripts_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_enqueue;
use function OutletPro\enqueue_init;

class Test_Register_Block_Cart_Scripts_Hook extends WP_UnitTestCase {
	public function test_registers_block_cart_scripts_without_enqueuing_them(): void {
		// Arrange.
		deinit_enqueue();
		enqueue_init();

		// Act.
		do_action( 'wp_enqueue_scripts' );

		// Assert.
		$this->assertTrue( wp_script_is( 'outletpro-block-cart', 'registered' ) );
		$this->assertFalse( wp_script_is( 'outletpro-block-cart', 'enqueued' ) );
	}
}
