<?php
/**
 * Tests for enqueue_admin_canvas_scripts_hook().
 *
 * @package OutletPro
 */

use function OutletPro\deinit_enqueue;
use function OutletPro\enqueue_init;

class Test_Enqueue_Admin_Canvas_Scripts_Hook extends WP_UnitTestCase {

	public function test_enqueues_admin_canvas_script_in_admin(): void {
		// Arrange.
		deinit_enqueue();
		enqueue_init();
		set_current_screen( 'dashboard' );

		// Act.
		do_action( 'enqueue_block_assets' );

		// Assert.
		$this->assertTrue( wp_script_is( 'outletpro-admin-canvas-scripts', 'enqueued' ) );
	}

	public function test_does_not_enqueue_admin_canvas_script_on_front_end(): void {
		// Arrange.
		deinit_enqueue();
		enqueue_init();
		set_current_screen( 'front' );

		// Act.
		do_action( 'enqueue_block_assets' );

		// Assert.
		$this->assertFalse( wp_script_is( 'outletpro-admin-canvas-scripts', 'enqueued' ) );
	}
}
