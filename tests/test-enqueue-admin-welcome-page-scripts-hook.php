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
}
