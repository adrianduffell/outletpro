<?php
/**
 * Tests for add_plugin_action_links_hook().
 *
 * @package OutletPro
 */

use function OutletPro\add_plugin_action_links_hook;
use const OutletPro\LICENSE_PAGE_SLUG;

class Test_Add_Plugin_Action_Links_Hook extends WP_UnitTestCase {

	public function test_adds_settings_link_to_plugin_action_links(): void {
		// Arrange.
		$links = array( '<a href="#">Deactivate</a>' );

		// Act.
		$result = add_plugin_action_links_hook( $links );

		// Assert.
		$this->assertStringContainsString( 'Settings', $result[0] );
	}

	public function test_settings_link_points_to_license_page(): void {
		// Arrange.
		$links = array();

		// Act.
		$result = add_plugin_action_links_hook( $links );

		// Assert.
		$this->assertStringContainsString( LICENSE_PAGE_SLUG, $result[0] );
	}

	public function test_settings_link_is_prepended_before_existing_links(): void {
		// Arrange.
		$links = array( '<a href="#">Deactivate</a>' );

		// Act.
		$result = add_plugin_action_links_hook( $links );

		// Assert.
		$this->assertCount( 2, $result );
		$this->assertStringContainsString( 'Settings', $result[0] );
		$this->assertStringContainsString( 'Deactivate', $result[1] );
	}

	public function test_settings_link_contains_admin_url(): void {
		// Arrange.
		$links = array();

		// Act.
		$result = add_plugin_action_links_hook( $links );

		// Assert.
		$this->assertStringContainsString( 'admin.php', $result[0] );
	}
}
