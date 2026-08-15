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

	public function test_localizes_the_hostname(): void {
		// Arrange.
		add_filter( 'home_url', fn(): string => 'https://shop.local', 10, 0 );
		set_current_screen( 'toplevel_page_outletpro-welcome' );
		wp_dequeue_script( 'outletpro-welcome-page' );
		wp_deregister_script( 'outletpro-welcome-page' );

		// Act.
		enqueue_admin_welcome_page_scripts_hook();
		$data = wp_scripts()->get_data( 'outletpro-welcome-page', 'data' );

		// Assert.
		$this->assertIsString( $data );
		$this->assertStringContainsString( '"hostname":"shop.local"', $data );
	}

	public function test_localizes_the_local_host_status(): void {
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
		$this->assertStringContainsString( '"isLocalHost":"1"', $data );
	}

	public function test_localizes_the_environment_type(): void {
		// Arrange.
		set_current_screen( 'toplevel_page_outletpro-welcome' );
		wp_dequeue_script( 'outletpro-welcome-page' );
		wp_deregister_script( 'outletpro-welcome-page' );

		// Act.
		enqueue_admin_welcome_page_scripts_hook();
		$data = wp_scripts()->get_data( 'outletpro-welcome-page', 'data' );

		// Assert.
		$this->assertIsString( $data );
		$this->assertStringContainsString(
			'"environmentType":"' . wp_get_environment_type() . '"',
			$data
		);
	}

	public function test_localizes_empty_hostname_when_hostname_cannot_be_retrieved(): void {
		// Arrange.
		add_filter( 'home_url', fn(): string => 'http://:' );
		set_current_screen( 'toplevel_page_outletpro-welcome' );
		wp_dequeue_script( 'outletpro-welcome-page' );
		wp_deregister_script( 'outletpro-welcome-page' );

		// Act.
		enqueue_admin_welcome_page_scripts_hook();
		$data = wp_scripts()->get_data( 'outletpro-welcome-page', 'data' );

		// Assert.
		$this->assertIsString( $data );
		$this->assertStringContainsString( '"hostname":""', $data );
	}
}
