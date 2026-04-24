<?php
/**
 * Tests for register_classic_styles_hook().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\deinit_enqueue;
use function WC_Clearance\enqueue_init;

class Test_Register_Classic_Styles_Hook extends WP_UnitTestCase {

	public function test_registers_classic_badge_style(): void {
		// Arrange.
		wp_deregister_style( 'wc-clearance-classic-badge' );
		deinit_enqueue();
		enqueue_init();

		// Act.
		do_action( 'wp_enqueue_scripts' );

		// Assert.
		$this->assertTrue( wp_style_is( 'wc-clearance-classic-badge', 'registered' ) );
	}

	public function test_registers_classic_message_style(): void {
		// Arrange.
		wp_deregister_style( 'wc-clearance-classic-message' );
		deinit_enqueue();
		enqueue_init();

		// Act.
		do_action( 'wp_enqueue_scripts' );

		// Assert.
		$this->assertTrue( wp_style_is( 'wc-clearance-classic-message', 'registered' ) );
	}
}
