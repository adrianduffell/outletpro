<?php
/**
 * Tests for add_plugin_action_links_hook().
 *
 * @package OutletPro
 * @group license
 * @copyright © 2026 Adrian Duffell
 */

use function OutletPro\init_license;
use const OutletPro\PLUGIN_FILE;
use const OutletPro\WELCOME_PAGE_SLUG;

class Test_Add_Plugin_Action_Links_Hook extends WP_UnitTestCase {

	public function test_adds_setup_link_to_plugin_action_links(): void {
		// Arrange.
		init_license();
		$links = array( '<a href="#">Deactivate</a>' );

		// Act.
		$result = apply_filters( 'plugin_action_links_' . plugin_basename( PLUGIN_FILE ), $links );

		// Assert.
		$this->assertStringContainsString( 'Setup', $result[0] );
	}

	public function test_setup_link_points_to_welcome_page(): void {
		// Arrange.
		init_license();
		$links = array();

		// Act.
		$result = apply_filters( 'plugin_action_links_' . plugin_basename( PLUGIN_FILE ), $links );

		// Assert.
		$this->assertStringContainsString( '/wp-admin/admin.php?page=' . WELCOME_PAGE_SLUG, $result[0] );
	}

	public function test_setup_link_is_prepended_before_existing_links(): void {
		// Arrange.
		init_license();
		$links = array( '<a href="#">Deactivate</a>' );

		// Act.
		$result = apply_filters( 'plugin_action_links_' . plugin_basename( PLUGIN_FILE ), $links );

		// Assert.
		$this->assertCount( 2, $result );
		$this->assertStringContainsString( 'Setup', $result[0] );
		$this->assertStringContainsString( 'Deactivate', $result[1] );
	}
}
