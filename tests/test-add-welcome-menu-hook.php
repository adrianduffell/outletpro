<?php
/**
 * Tests for add_welcome_menu_hook().
 *
 * @package OutletPro
 */

use function OutletPro\deinit_welcome_page;
use function OutletPro\init_welcome_page;
use const OutletPro\HAS_LICENSE_TRANSIENT;
use const OutletPro\LICENSE_KEY_OPTION;
use const OutletPro\WELCOME_PAGE_SLUG;

class Test_Add_Welcome_Menu_Hook extends WP_UnitTestCase {

	public function test_registers_menu_page_when_no_license(): void {
		// Arrange.
		delete_option( LICENSE_KEY_OPTION );
		delete_transient( HAS_LICENSE_TRANSIENT );
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		deinit_welcome_page();
		init_welcome_page();

		// Act.
		do_action( 'admin_menu' );

		// Assert.
		$this->assertNotNull( menu_page_url( WELCOME_PAGE_SLUG, false ) );
	}

	public function test_does_not_register_menu_page_when_license_is_active(): void {
		// Arrange.
		delete_transient( HAS_LICENSE_TRANSIENT );
		update_option( LICENSE_KEY_OPTION, 'valid-license-key' );
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		deinit_welcome_page();
		init_welcome_page();

		// Act.
		do_action( 'admin_menu' );

		// Assert.
		$this->assertFalse( menu_page_url( WELCOME_PAGE_SLUG, false ) );

		// Cleanup.
		delete_option( LICENSE_KEY_OPTION );
		delete_transient( HAS_LICENSE_TRANSIENT );
	}
}
