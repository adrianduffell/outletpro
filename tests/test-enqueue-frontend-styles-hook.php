<?php
/**
 * Tests for enqueue_frontend_styles_hook().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\enqueue_frontend_styles_hook;

class Test_Enqueue_Frontend_Styles_Hook extends WP_UnitTestCase {

	public function test_hook_is_registered(): void {
		// Assert.
		$this->assertSame( 10, has_action( 'wp_enqueue_scripts', 'WC_Clearance\enqueue_frontend_styles_hook' ) );
	}

	public function test_enqueues_clearance_stylesheet(): void {
		// Arrange.
		wp_deregister_style( 'wc-clearance' );
		wp_dequeue_style( 'wc-clearance' );

		// Act.
		enqueue_frontend_styles_hook();

		// Assert.
		$this->assertTrue( wp_style_is( 'wc-clearance', 'enqueued' ) );
	}
}
