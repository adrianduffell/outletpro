<?php
/**
 * Tests for enqueue_cart_styles_hook().
 *
 * @package OutletPro
 */

use function OutletPro\deinit_enqueue;
use function OutletPro\enqueue_init;

class Test_Enqueue_Cart_Styles_Hook extends WP_UnitTestCase {

	public function test_enqueues_cart_style(): void {
		// Arrange.
		deinit_enqueue();
		enqueue_init();

		// Act.
		do_action( 'wp_enqueue_scripts' );

		// Assert.
		$this->assertTrue( wp_style_is( 'outletpro-cart-badge', 'enqueued' ) );
	}
}
