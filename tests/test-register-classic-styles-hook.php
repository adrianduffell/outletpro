<?php
/**
 * Tests for register_classic_styles_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_enqueue;
use function OutletPro\enqueue_init;

class Test_Register_Classic_Styles_Hook extends WP_UnitTestCase {

	public function test_registers_classic_badge_style(): void {
		// Arrange.
		deinit_enqueue();
		enqueue_init();

		// Act.
		do_action( 'wp_enqueue_scripts' );

		// Assert.
		$this->assertTrue( wp_style_is( 'outletpro-classic-badge', 'registered' ) );
	}

	public function test_registers_classic_message_style(): void {
		// Arrange.
		deinit_enqueue();
		enqueue_init();

		// Act.
		do_action( 'wp_enqueue_scripts' );

		// Assert.
		$this->assertTrue( wp_style_is( 'outletpro-classic-message', 'registered' ) );
	}
}
