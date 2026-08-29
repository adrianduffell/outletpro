<?php
/**
 * Tests for add_premium_license_notice_hook().
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\init_license;
use const OutletPro\LICENSE_STATUS_TRANSIENT;
use const OutletPro\PLUGIN_FILE;
use const OutletPro\WELCOME_PAGE_SLUG;

class Test_Add_Premium_License_Notice_Hook extends WP_UnitTestCase {

	public function test_renders_notice_when_license_is_missing(): void {
		// Arrange.
		init_license();
		set_transient( LICENSE_STATUS_TRANSIENT, 'none', WEEK_IN_SECONDS );

		// Expect.
		$this->expectOutputRegex( '/A premium license is needed for Outlet Pro to receive updates\./' );

		// Act.
		do_action( 'after_plugin_row_' . plugin_basename( PLUGIN_FILE ), plugin_basename( PLUGIN_FILE ), array(), 'all' );
	}

	public function test_notice_links_to_welcome_screen_when_license_is_missing(): void {
		// Arrange.
		init_license();
		set_transient( LICENSE_STATUS_TRANSIENT, 'none', WEEK_IN_SECONDS );
		$welcome_url = preg_quote( esc_url( admin_url( 'admin.php?page=' . WELCOME_PAGE_SLUG ) ), '/' );

		// Expect.
		$this->expectOutputRegex( '/<a\s[^>]*href="' . $welcome_url . '"[^>]*>/' );

		// Act.
		do_action( 'after_plugin_row_' . plugin_basename( PLUGIN_FILE ), plugin_basename( PLUGIN_FILE ), array(), 'all' );
	}

	public function test_does_not_render_notice_when_license_is_valid(): void {
		// Arrange.
		init_license();
		set_transient( LICENSE_STATUS_TRANSIENT, 'active', WEEK_IN_SECONDS );

		// Expect.
		$this->expectOutputString( '' );

		// Act.
		do_action( 'after_plugin_row_' . plugin_basename( PLUGIN_FILE ), plugin_basename( PLUGIN_FILE ), array(), 'all' );
	}
}
