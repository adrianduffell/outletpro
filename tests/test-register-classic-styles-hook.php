<?php
/**
 * Tests for register_classic_styles_hook().
 *
 * @package WC_Outlet
 */

use function WC_Outlet\deinit_enqueue;
use function WC_Outlet\enqueue_init;

class Test_Register_Classic_Styles_Hook extends WP_UnitTestCase {

	public function test_registers_classic_badge_style(): void {
		// Arrange.
		deinit_enqueue();
		enqueue_init();

		// Act.
		do_action( 'wp_enqueue_scripts' );

		// Assert.
		$this->assertTrue( wp_style_is( 'wc-outlet-classic-badge', 'registered' ) );
	}

	public function test_registers_classic_message_style(): void {
		// Arrange.
		deinit_enqueue();
		enqueue_init();

		// Act.
		do_action( 'wp_enqueue_scripts' );

		// Assert.
		$this->assertTrue( wp_style_is( 'wc-outlet-classic-message', 'registered' ) );
	}
}
