<?php
/**
 * Tests for enqueue_admin_welcome_page_scripts_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\enqueue_admin_welcome_page_scripts_hook;

class Test_Enqueue_Admin_Welcome_Page_Scripts_Hook extends WP_UnitTestCase {

	public function test_localizes_the_hostname_and_local_environment_status(): void {
		// Arrange.
		add_filter( 'home_url', fn(): string => 'https://shop.local' );
		set_current_screen( 'toplevel_page_outletpro-welcome' );
		wp_dequeue_script( 'outletpro-welcome-page' );
		wp_deregister_script( 'outletpro-welcome-page' );

		// Act.
		enqueue_admin_welcome_page_scripts_hook();
		$data = wp_scripts()->get_data( 'outletpro-welcome-page', 'data' );

		// Assert.
		$this->assertIsString( $data );
		$this->assertStringContainsString( '"hostname":"shop.local"', $data );
		$this->assertStringContainsString( '"isLocalEnvironment":"1"', $data );
	}
}
