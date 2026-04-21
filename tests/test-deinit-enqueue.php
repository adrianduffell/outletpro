<?php
/**
 * Tests for deinit_enqueue().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\deinit_enqueue;
use function WC_Clearance\enqueue_init;

class Test_Deinit_Enqueue extends WP_UnitTestCase {

	public function test_removes_wp_enqueue_scripts_hook(): void {
		// Arrange.
		enqueue_init();

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( has_action( 'wp_enqueue_scripts', 'WC_Clearance\register_classic_styles_hook' ) );
	}

	public function test_removes_admin_enqueue_scripts_styles_hook(): void {
		// Arrange.
		enqueue_init();

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( has_action( 'admin_enqueue_scripts', 'WC_Clearance\enqueue_admin_styles_hook' ) );
	}

	public function test_removes_admin_enqueue_scripts_product_scripts_hook(): void {
		// Arrange.
		enqueue_init();

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( has_action( 'admin_enqueue_scripts', 'WC_Clearance\enqueue_admin_product_scripts_hook' ) );
	}

	public function test_removes_enqueue_block_editor_assets_hook(): void {
		// Arrange.
		enqueue_init();

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( has_action( 'enqueue_block_editor_assets', 'WC_Clearance\enqueue_build_assets_hook' ) );
	}
}
