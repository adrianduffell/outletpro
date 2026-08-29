<?php
/**
 * Tests for enqueue_admin_welcome_page_scripts_hook().
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\license_enqueue_init;
use const OutletPro\LICENSE_ACTIVATION_OPTION;
use const OutletPro\LICENSE_NAME_TRANSIENT;
use const OutletPro\LICENSE_STATUS_TRANSIENT;

class Test_Enqueue_Admin_Welcome_Page_Scripts_Hook extends WP_UnitTestCase {

	public function test_localizes_the_environment_type(): void {
		// Arrange.
		set_current_screen( 'toplevel_page_outletpro-welcome' );
		wp_dequeue_script( 'outletpro-welcome-page' );
		wp_deregister_script( 'outletpro-welcome-page' );
		remove_all_actions( 'admin_enqueue_scripts' );
		license_enqueue_init();

		// Act.
		do_action( 'admin_enqueue_scripts' );
		$data = wp_scripts()->get_data( 'outletpro-welcome-page', 'data' );

		// Assert.
		$this->assertIsString( $data );
		$this->assertStringContainsString(
			'"environmentType":"' . wp_get_environment_type() . '"',
			$data
		);
	}

	public function test_localizes_the_license_status(): void {
		// Arrange.
		set_current_screen( 'toplevel_page_outletpro-welcome' );
		wp_dequeue_script( 'outletpro-welcome-page' );
		wp_deregister_script( 'outletpro-welcome-page' );
		remove_all_actions( 'admin_enqueue_scripts' );
		set_transient( LICENSE_STATUS_TRANSIENT, 'not_found' );
		license_enqueue_init();

		// Act.
		do_action( 'admin_enqueue_scripts' );
		$data = wp_scripts()->get_data( 'outletpro-welcome-page', 'data' );

		// Assert.
		$this->assertIsString( $data );
		$this->assertStringContainsString( '"licenseStatus":"not_found"', $data );
	}

	public function test_localizes_the_expired_license_name(): void {
		// Arrange.
		set_current_screen( 'toplevel_page_outletpro-welcome' );
		wp_dequeue_script( 'outletpro-welcome-page' );
		wp_deregister_script( 'outletpro-welcome-page' );
		remove_all_actions( 'admin_enqueue_scripts' );
		set_transient( LICENSE_STATUS_TRANSIENT, 'expired' );
		set_transient( LICENSE_NAME_TRANSIENT, 'Long-term service' );
		license_enqueue_init();

		// Act.
		do_action( 'admin_enqueue_scripts' );
		$data = wp_scripts()->get_data( 'outletpro-welcome-page', 'data' );

		// Assert.
		$this->assertIsString( $data );
		$this->assertStringContainsString( '"licenseName":"Long-term service"', $data );
	}

	public function test_localizes_the_active_license_name(): void {
		// Arrange.
		set_current_screen( 'toplevel_page_outletpro-welcome' );
		wp_dequeue_script( 'outletpro-welcome-page' );
		wp_deregister_script( 'outletpro-welcome-page' );
		remove_all_actions( 'admin_enqueue_scripts' );
		set_transient( LICENSE_STATUS_TRANSIENT, 'active' );
		set_transient( LICENSE_NAME_TRANSIENT, 'Lifetime' );
		license_enqueue_init();

		// Act.
		do_action( 'admin_enqueue_scripts' );
		$data = wp_scripts()->get_data( 'outletpro-welcome-page', 'data' );

		// Assert.
		$this->assertIsString( $data );
		$this->assertStringContainsString( '"licenseName":"Lifetime"', $data );
	}

	public function test_uses_safe_default_when_license_name_cannot_be_retrieved(): void {
		// Arrange.
		set_current_screen( 'toplevel_page_outletpro-welcome' );
		wp_dequeue_script( 'outletpro-welcome-page' );
		wp_deregister_script( 'outletpro-welcome-page' );
		remove_all_actions( 'admin_enqueue_scripts' );
		delete_option( LICENSE_ACTIVATION_OPTION );
		set_transient( LICENSE_STATUS_TRANSIENT, 'expired' );
		delete_transient( LICENSE_NAME_TRANSIENT );
		license_enqueue_init();

		// Act.
		do_action( 'admin_enqueue_scripts' );
		$data = wp_scripts()->get_data( 'outletpro-welcome-page', 'data' );

		// Assert.
		$this->assertIsString( $data );
		$this->assertStringContainsString( '"licenseStatus":"expired"', $data );
		$this->assertStringContainsString( '"licenseName":""', $data );
	}
}
