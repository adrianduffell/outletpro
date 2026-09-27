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
use const OutletPro\OUTLET_BADGE_LABEL_OPTION;

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
		$this->assertContains( 'wc-blocks-checkout', wp_scripts()->registered['outletpro-block-cart']->deps );
		$this->assertStringEndsWith( 'assets/js/block-cart.js', wp_scripts()->registered['outletpro-block-cart']->src );
	}

	public function test_exposes_badge_label_to_block_cart_script(): void {
		// Arrange.
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Big "Clearance"' );
		deinit_enqueue();
		enqueue_init();

		// Act.
		do_action( 'wp_enqueue_scripts' );

		// Assert.
		$data = wp_scripts()->get_data( 'outletpro-block-cart', 'data' );
		$this->assertIsString( $data );
		$this->assertStringContainsString( 'var __experimentalOutletProBlockCart = ', $data );
		$this->assertStringContainsString( '"badgeLabel":"Big \"Clearance\""', $data );

		delete_option( OUTLET_BADGE_LABEL_OPTION );
	}

	public function test_exposes_empty_label_for_non_string_setting(): void {
		// Arrange.
		update_option( OUTLET_BADGE_LABEL_OPTION, array( 'invalid' ) );
		deinit_enqueue();
		enqueue_init();

		// Act.
		do_action( 'wp_enqueue_scripts' );

		// Assert.
		$data = wp_scripts()->get_data( 'outletpro-block-cart', 'data' );
		$this->assertIsString( $data );
		$this->assertStringContainsString( '"badgeLabel":""', $data );

		delete_option( OUTLET_BADGE_LABEL_OPTION );
	}
}
