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
		// Act.
		do_action( 'admin_enqueue_scripts', 'post.php' );
		// Assert.
		$this->assertTrue( wp_style_is( 'outletpro-outlet-editor-components', 'enqueued' ) );
		$this->assertSame( plugin_dir_url( PLUGIN_FILE ) . 'build/outlet-editor.css', wp_styles()->registered['outletpro-outlet-editor-components']->src );
		$this->assertStringEndsWith( '/build/style-outlet-editor.css', wp_styles()->registered['outletpro-outlet-editor']->src );
		$this->assertContains( 'wp-components', wp_styles()->registered['outletpro-outlet-editor']->deps );
		$this->assertContains( 'wp-commands', wp_scripts()->registered['outletpro-outlet-editor']->deps );
	}
}
