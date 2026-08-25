<?php
/**
 * Tests for add_premium_license_notice_hook().
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_license;
use function OutletPro\init_license;
use const OutletPro\LICENSE_STATUS_TRANSIENT;
use const OutletPro\PLUGIN_FILE;

class Test_Add_Premium_License_Notice_Hook extends WP_UnitTestCase {

	public function test_renders_notice_when_license_is_missing(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		init_license();
		set_transient( LICENSE_STATUS_TRANSIENT, 'none', WEEK_IN_SECONDS );

		// Expect.
		$this->expectOutputRegex( '/Outlet Pro requires a premium license for plugin updates\..*https:\/\/outletpro\.zip\/premium-license.*target="_blank".*Learn more/s' );

		// Act.
		try {
			do_action( 'after_plugin_row_' . plugin_basename( PLUGIN_FILE ), plugin_basename( PLUGIN_FILE ), array(), 'all' );
		} finally {
			// Cleanup.
			delete_transient( LICENSE_STATUS_TRANSIENT );
			deinit_license();
		}
	}

	public function test_does_not_render_notice_when_license_is_valid(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		init_license();
		set_transient( LICENSE_STATUS_TRANSIENT, 'active', WEEK_IN_SECONDS );

		// Expect.
		$this->expectOutputString( '' );

		// Act.
		try {
			do_action( 'after_plugin_row_' . plugin_basename( PLUGIN_FILE ), plugin_basename( PLUGIN_FILE ), array(), 'all' );
		} finally {
			// Cleanup.
			delete_transient( LICENSE_STATUS_TRANSIENT );
			deinit_license();
		}
	}
}
