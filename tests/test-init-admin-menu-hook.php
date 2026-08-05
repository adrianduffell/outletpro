<?php
/**
 * Tests for add_welcome_menu_hook().
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_admin_menu;
use function OutletPro\init_admin_menu;
use const OutletPro\HAS_LICENSE_TRANSIENT;
use const OutletPro\LICENSE_KEY_OPTION;

class Test_Add_Welcome_Menu_Hook extends WP_UnitTestCase {

	public function test_registers_menu_page_when_no_license(): void {
		// Arrange.
		delete_option( LICENSE_KEY_OPTION );
		delete_transient( HAS_LICENSE_TRANSIENT );
		deinit_admin_menu();

		// Act.
		init_admin_menu();

		// Assert.
		$this->assertIsInt( has_action( 'admin_menu', 'OutletPro\add_welcome_menu_hook' ) );
	}

	public function test_does_not_register_menu_page_when_license_is_active(): void {
		// Arrange.
		delete_transient( HAS_LICENSE_TRANSIENT );
		update_option( LICENSE_KEY_OPTION, 'valid-license-key' );
		deinit_admin_menu();

		// Act.
		init_admin_menu();

		// Assert.
		$this->assertFalse( has_action( 'admin_menu', 'OutletPro\add_welcome_menu_hook' ) );
	}
}
