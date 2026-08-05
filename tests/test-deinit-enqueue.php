<?php
/**
 * Tests for deinit_enqueue().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_enqueue;
use function OutletPro\enqueue_init;

class Test_Deinit_Enqueue extends WP_UnitTestCase {

	public function test_removes_register_classic_styles_hook(): void {
		// Arrange.
		enqueue_init();

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( has_action( 'wp_enqueue_scripts', 'OutletPro\register_classic_styles_hook' ) );
	}

	public function test_removes_enqueue_cart_styles_hook(): void {
		// Arrange.
		enqueue_init();

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( has_action( 'wp_enqueue_scripts', 'OutletPro\enqueue_cart_styles_hook' ) );
	}

	public function test_removes_output_badge_style_css_variables_hook(): void {
		// Arrange.
		enqueue_init();

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( has_action( 'wp_head', 'OutletPro\output_badge_style_css_variables_hook' ) );
	}

	public function test_removes_enqueue_admin_editor_styles_hook(): void {
		// Arrange.
		enqueue_init();

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( has_action( 'enqueue_block_assets', 'OutletPro\enqueue_admin_editor_styles_hook' ) );
	}

	public function test_removes_enqueue_admin_canvas_scripts_hook(): void {
		// Arrange.
		enqueue_init();

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( has_action( 'enqueue_block_assets', 'OutletPro\enqueue_admin_canvas_scripts_hook' ) );
	}

	public function test_removes_admin_enqueue_scripts_styles_hook(): void {
		// Arrange.
		enqueue_init();

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( has_action( 'admin_enqueue_scripts', 'OutletPro\enqueue_admin_styles_hook' ) );
	}

	public function test_removes_admin_enqueue_scripts_product_scripts_hook(): void {
		// Arrange.
		enqueue_init();

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( has_action( 'admin_enqueue_scripts', 'OutletPro\enqueue_admin_product_scripts_hook' ) );
	}

	public function test_removes_admin_enqueue_scripts_welcome_page_scripts_hook(): void {
		// Arrange.
		enqueue_init();

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( has_action( 'admin_enqueue_scripts', 'OutletPro\enqueue_admin_welcome_page_scripts_hook' ) );
	}

	public function test_removes_enqueue_block_editor_assets_hook(): void {
		// Arrange.
		enqueue_init();

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( has_action( 'enqueue_block_editor_assets', 'OutletPro\enqueue_build_assets_hook' ) );
	}

	public function test_deregisters_block_styles(): void {
		// Arrange.
		wp_register_style( 'outletpro-badge-block', false, array(), 'test' );

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( wp_style_is( 'outletpro-badge-block', 'registered' ) );
	}

	public function test_safely_handles_block_styles_not_registered(): void {
		// Arrange.

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( wp_style_is( 'outletpro-badge-block', 'registered' ) );
	}

	public function test_deregisters_cart_style(): void {
		// Arrange.
		wp_register_style( 'outletpro-cart-badge', false, array(), 'test' );

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( wp_style_is( 'outletpro-cart-badge', 'registered' ) );
	}

	public function test_safely_handles_cart_style_not_registered(): void {
		// Arrange - 'outletpro-cart-badge' is not registered.

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( wp_style_is( 'outletpro-cart-badge', 'registered' ) );
	}

	public function test_deregisters_admin_styles(): void {
		// Arrange.
		wp_register_style( 'outletpro-admin', false, array(), 'test' );

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( wp_style_is( 'outletpro-admin', 'registered' ) );
	}

	public function test_safely_handles_admin_styles_not_registered(): void {
		// Arrange - 'outletpro-admin' is not registered.

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( wp_style_is( 'outletpro-admin', 'registered' ) );
	}

	public function test_deregisters_admin_editor_styles(): void {
		// Arrange.
		wp_register_style( 'outletpro-admin-editor', false, array(), 'test' );

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( wp_style_is( 'outletpro-admin-editor', 'registered' ) );
	}

	public function test_safely_handles_admin_editor_styles_not_registered(): void {
		// Arrange - 'outletpro-admin-editor' is not registered.

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( wp_style_is( 'outletpro-admin-editor', 'registered' ) );
	}

	public function test_deregisters_admin_product_script(): void {
		// Arrange.
		wp_register_script( 'outletpro-products-admin', false, array(), 'test', true );

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( wp_script_is( 'outletpro-products-admin', 'registered' ) );
	}

	public function test_safely_handles_admin_product_script_not_registered(): void {
		// Arrange - 'outletpro-products-admin' is not registered.

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( wp_script_is( 'outletpro-products-admin', 'registered' ) );
	}

	public function test_deregisters_welcome_page_script(): void {
		// Arrange.
		wp_register_script( 'outletpro-welcome-page', false, array(), 'test', true );

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( wp_script_is( 'outletpro-welcome-page', 'registered' ) );
	}

	public function test_safely_handles_welcome_page_script_not_registered(): void {
		// Arrange - 'outletpro-welcome-page' is not registered.

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( wp_script_is( 'outletpro-welcome-page', 'registered' ) );
	}

	public function test_deregisters_build_script(): void {
		// Arrange.
		wp_register_script( 'outletpro-editor', false, array(), 'test', true );

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( wp_script_is( 'outletpro-editor', 'registered' ) );
	}

	public function test_safely_handles_build_script_not_registered(): void {
		// Arrange - 'outletpro-editor' is not registered.

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( wp_script_is( 'outletpro-editor', 'registered' ) );
	}

	public function test_deregisters_admin_canvas_script(): void {
		// Arrange.
		wp_register_script( 'outletpro-admin-canvas-scripts', false, array(), 'test', false );

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( wp_script_is( 'outletpro-admin-canvas-scripts', 'registered' ) );
	}

	public function test_safely_handles_admin_canvas_script_not_registered(): void {
		// Arrange - 'outletpro-admin-canvas-scripts' is not registered.

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( wp_script_is( 'outletpro-admin-canvas-scripts', 'registered' ) );
	}
}
