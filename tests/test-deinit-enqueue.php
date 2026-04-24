<?php
/**
 * Tests for deinit_enqueue().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\deinit_enqueue;
use function WC_Clearance\enqueue_init;

class Test_Deinit_Enqueue extends WP_UnitTestCase {

	public function test_removes_register_classic_styles_hook(): void {
		// Arrange.
		enqueue_init();

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( has_action( 'wp_enqueue_scripts', 'WC_Clearance\register_classic_styles_hook' ) );
	}

	public function test_removes_enqueue_cart_styles_hook(): void {
		// Arrange.
		enqueue_init();

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( has_action( 'wp_enqueue_scripts', 'WC_Clearance\enqueue_cart_styles_hook' ) );
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

	public function test_deregisters_block_styles(): void {
		// Arrange.
		wp_register_style( 'wc-clearance-badge-block', false, array(), 'test' );

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( wp_style_is( 'wc-clearance-badge-block', 'registered' ) );
	}

	public function test_safely_handles_block_styles_not_registered(): void {
		// Arrange.

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( wp_style_is( 'wc-clearance-badge-block', 'registered' ) );
	}

	public function test_deregisters_cart_style(): void {
		// Arrange.
		wp_register_style( 'wc-clearance-cart-badge', false, array(), 'test' );

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( wp_style_is( 'wc-clearance-cart-badge', 'registered' ) );
	}

	public function test_safely_handles_cart_style_not_registered(): void {
		// Arrange - 'wc-clearance-cart-badge' is not registered.

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( wp_style_is( 'wc-clearance-cart-badge', 'registered' ) );
	}

	public function test_deregisters_admin_styles(): void {
		// Arrange.
		wp_register_style( 'wc-clearance-admin', false, array(), 'test' );

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( wp_style_is( 'wc-clearance-admin', 'registered' ) );
	}

	public function test_safely_handles_admin_styles_not_registered(): void {
		// Arrange - 'wc-clearance-admin' is not registered.

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( wp_style_is( 'wc-clearance-admin', 'registered' ) );
	}

	public function test_deregisters_admin_product_script(): void {
		// Arrange.
		wp_register_script( 'wc-clearance-products-admin', false, array(), 'test', true );

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( wp_script_is( 'wc-clearance-products-admin', 'registered' ) );
	}

	public function test_safely_handles_admin_product_script_not_registered(): void {
		// Arrange - 'wc-clearance-products-admin' is not registered.

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( wp_script_is( 'wc-clearance-products-admin', 'registered' ) );
	}

	public function test_deregisters_build_script(): void {
		// Arrange.
		wp_register_script( 'wc-clearance-editor', false, array(), 'test', true );

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( wp_script_is( 'wc-clearance-editor', 'registered' ) );
	}

	public function test_safely_handles_build_script_not_registered(): void {
		// Arrange - 'wc-clearance-editor' is not registered.

		// Act.
		deinit_enqueue();

		// Assert.
		$this->assertFalse( wp_script_is( 'wc-clearance-editor', 'registered' ) );
	}
}
