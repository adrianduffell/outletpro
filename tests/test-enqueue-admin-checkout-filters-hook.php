<?php
/**
 * Tests for enqueue_admin_checkout_filters_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_enqueue;
use function OutletPro\enqueue_init;

class Test_Enqueue_Admin_Checkout_Filters_Hook extends WP_UnitTestCase {

	public function test_enqueues_checkout_filters_in_editor(): void {
		// Arrange.
		deinit_enqueue();
		enqueue_init();
		set_current_screen( 'site-editor' );

		// Act.
		do_action( 'enqueue_block_editor_assets' );

		// Assert.
		$this->assertTrue( wp_script_is( 'outletpro-checkout-filters', 'enqueued' ) );
		$this->assertSame( array( 'wc-blocks-checkout' ), wp_scripts()->registered['outletpro-checkout-filters']->deps );
		$this->assertSame( 1, wp_scripts()->get_data( 'outletpro-checkout-filters', 'group' ) );
	}

	public function test_does_not_enqueue_checkout_filters_in_canvas(): void {
		// Arrange.
		deinit_enqueue();
		enqueue_init();
		set_current_screen( 'site-editor' );

		// Act.
		do_action( 'enqueue_block_assets' );

		// Assert.
		$this->assertFalse( wp_script_is( 'outletpro-checkout-filters', 'enqueued' ) );
	}
}
