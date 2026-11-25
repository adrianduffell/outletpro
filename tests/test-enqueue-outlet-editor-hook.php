<?php
/**
 * Outlet editor asset loading.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use const OutletPro\PLUGIN_FILE;

class Test_Enqueue_Outlet_Editor_Hook extends WP_UnitTestCase {
	public function test_loads_both_generated_stylesheets_and_wordpress_dependencies(): void {
		// Arrange.
		set_current_screen( 'page' );
		add_action( 'admin_enqueue_scripts', 'OutletPro\\enqueue_outlet_editor_hook' );
		$has_core_data_styles = is_wp_version_compatible( '7.2' );
		// Act.
		do_action( 'admin_enqueue_scripts', 'post.php' );
		// Assert.
		$this->assertTrue( wp_style_is( 'outletpro-outlet-editor-components', 'enqueued' ) );
		$this->assertSame( plugin_dir_url( PLUGIN_FILE ) . 'build/outlet-editor.css', wp_styles()->registered['outletpro-outlet-editor-components']->src );
		$this->assertStringEndsWith( '/build/style-outlet-editor.css', wp_styles()->registered['outletpro-outlet-editor']->src );
		$this->assertContains( 'wp-components', wp_styles()->registered['outletpro-outlet-editor']->deps );
		$this->assertContains( 'wp-editor', wp_styles()->registered['outletpro-outlet-editor']->deps );
		$this->assertContains( 'outletpro-editor', wp_styles()->registered['outletpro-outlet-editor']->deps );
		$this->assertTrue( wp_style_is( 'outletpro-editor', 'enqueued' ) );
		$this->assertStringEndsWith( '/build/style-index.css', wp_styles()->registered['outletpro-editor']->src );
		$this->assertContains( 'wp-commands', wp_scripts()->registered['outletpro-outlet-editor']->deps );
		$this->assertContains( 'wc-settings', wp_scripts()->registered['outletpro-outlet-editor']->deps );
		$this->assertSame( $has_core_data_styles, in_array( 'wp-core-data', wp_styles()->registered['outletpro-outlet-editor']->deps, true ) );
		$this->assertSame( $has_core_data_styles, wp_style_is( 'wp-core-data', 'enqueued' ) );
	}
}
