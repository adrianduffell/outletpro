<?php
/**
 * Tests for dev build functions.
 *
 * @package OutletPro
 */

use function OutletPro\add_dev_admin_bar_node_hook;
use function OutletPro\enqueue_dev_assets_hook;
use function OutletPro\init_dev;
use function OutletPro\output_dev_splash_hook;

class Test_Dev extends WP_UnitTestCase {

	public function test_init_dev_registers_admin_bar_menu_hook(): void {
		// Arrange.
		remove_action( 'admin_bar_menu', 'OutletPro\add_dev_admin_bar_node_hook', 100 );

		// Act.
		init_dev();

		// Assert.
		$this->assertSame( 100, has_action( 'admin_bar_menu', 'OutletPro\add_dev_admin_bar_node_hook' ) );
	}

	public function test_init_dev_registers_admin_footer_hook(): void {
		// Arrange.
		remove_action( 'admin_footer', 'OutletPro\output_dev_splash_hook' );

		// Act.
		init_dev();

		// Assert.
		$this->assertNotFalse( has_action( 'admin_footer', 'OutletPro\output_dev_splash_hook' ) );
	}

	public function test_init_dev_registers_admin_enqueue_scripts_hook(): void {
		// Arrange.
		remove_action( 'admin_enqueue_scripts', 'OutletPro\enqueue_dev_assets_hook' );

		// Act.
		init_dev();

		// Assert.
		$this->assertNotFalse( has_action( 'admin_enqueue_scripts', 'OutletPro\enqueue_dev_assets_hook' ) );
	}

	public function test_add_dev_admin_bar_node_hook_adds_outletpro_dev_node(): void {
		// Arrange.
		$wp_admin_bar = new WP_Admin_Bar();

		// Act.
		add_dev_admin_bar_node_hook( $wp_admin_bar );

		// Assert.
		$this->assertNotNull( $wp_admin_bar->get_node( 'outletpro-dev' ) );
	}

	public function test_output_dev_splash_hook_outputs_splash_container(): void {
		// Expect.
		$this->expectOutputRegex( '/outletpro-dev-splash/' );

		// Act.
		output_dev_splash_hook();
	}

	public function test_output_dev_splash_hook_outputs_welcome_heading(): void {
		// Expect.
		$this->expectOutputRegex( '/Welcome to the Outlet Pro dev build/' );

		// Act.
		output_dev_splash_hook();
	}

	public function test_output_dev_splash_hook_outputs_disclaimer(): void {
		// Expect.
		$this->expectOutputRegex( '/Do not use on a live store/' );

		// Act.
		output_dev_splash_hook();
	}

	public function test_output_dev_splash_hook_outputs_github_link(): void {
		// Expect.
		$this->expectOutputRegex( '#https://github\.com/adrianduffell/outletpro/#' );

		// Act.
		output_dev_splash_hook();
	}

	public function test_output_dev_splash_hook_outputs_dismiss_button(): void {
		// Expect.
		$this->expectOutputRegex( '/outletpro-dev-splash-dismiss/' );

		// Act.
		output_dev_splash_hook();
	}

	public function test_enqueue_dev_assets_hook_enqueues_stylesheet(): void {
		// Arrange.
		wp_dequeue_style( 'outletpro-dev' );
		wp_deregister_style( 'outletpro-dev' );

		// Act.
		enqueue_dev_assets_hook();

		// Assert.
		$this->assertTrue( wp_style_is( 'outletpro-dev', 'enqueued' ) );
	}

	public function test_enqueue_dev_assets_hook_enqueues_script(): void {
		// Arrange.
		wp_dequeue_script( 'outletpro-dev' );
		wp_deregister_script( 'outletpro-dev' );

		// Act.
		enqueue_dev_assets_hook();

		// Assert.
		$this->assertTrue( wp_script_is( 'outletpro-dev', 'enqueued' ) );
	}
}
