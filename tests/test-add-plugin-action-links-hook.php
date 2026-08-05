<?php
/**
 * Tests for add_plugin_action_links_hook().
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_license;
use function OutletPro\init_license;
use const OutletPro\LICENSE_OPTIONS_GROUP;
use const OutletPro\PLUGIN_FILE;

class Test_Add_Plugin_Action_Links_Hook extends WP_UnitTestCase {

	public function test_adds_settings_link_to_plugin_action_links(): void {
		// Arrange.
		init_license();
		$links = array( '<a href="#">Deactivate</a>' );

		// Act.
		$result = apply_filters( 'plugin_action_links_' . plugin_basename( PLUGIN_FILE ), $links );

		// Assert.
		$this->assertStringContainsString( 'License', $result[0] );

		// Cleanup.
		deinit_license();
	}

	public function test_settings_link_points_to_license_page(): void {
		// Arrange.
		init_license();
		$links = array();

		// Act.
		$result = apply_filters( 'plugin_action_links_' . plugin_basename( PLUGIN_FILE ), $links );

		// Assert.
		$this->assertStringContainsString( LICENSE_OPTIONS_GROUP, $result[0] );

		// Cleanup.
		deinit_license();
	}

	public function test_settings_link_is_prepended_before_existing_links(): void {
		// Arrange.
		init_license();
		$links = array( '<a href="#">Deactivate</a>' );

		// Act.
		$result = apply_filters( 'plugin_action_links_' . plugin_basename( PLUGIN_FILE ), $links );

		// Assert.
		$this->assertCount( 2, $result );
		$this->assertStringContainsString( 'License', $result[0] );
		$this->assertStringContainsString( 'Deactivate', $result[1] );

		// Cleanup.
		deinit_license();
	}

	public function test_settings_link_contains_admin_url(): void {
		// Arrange.
		init_license();
		$links = array();

		// Act.
		$result = apply_filters( 'plugin_action_links_' . plugin_basename( PLUGIN_FILE ), $links );

		// Assert.
		$this->assertStringContainsString( 'admin.php', $result[0] );

		// Cleanup.
		deinit_license();
	}
}
