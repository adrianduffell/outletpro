<?php
/**
 * Tests for add_plugin_action_links_hook().
 *
 * @package OutletPro
 * @group license
 * @copyright © 2026 Adrian Duffell
 */

use function OutletPro\init_license;
use const OutletPro\LICENSE_EXPIRY_TRANSIENT;
use const OutletPro\LICENSE_NAME_TRANSIENT;
use const OutletPro\LICENSE_STATUS_TRANSIENT;
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

	public function test_adds_support_link_to_plugin_meta_links(): void {
		// Arrange.
		init_license();
		$links = array(
			'Version 1.0.0',
			'By <a href="https://adrianduffell.com">Adrian Duffell</a>',
		);

		// Act.
		$result = apply_filters( 'plugin_row_meta', $links, plugin_basename( PLUGIN_FILE ) );

		// Assert.
		$this->assertCount( 3, $result );
		$this->assertSame( $links[0], $result[0] );
		$this->assertSame( $links[1], $result[1] );
		$this->assertStringContainsString( 'https://outletpro.zip/support', $result[2] );
		$this->assertStringContainsString( 'Support', $result[2] );
	}

	public function test_adds_license_expiry_last(): void {
		// Arrange.
		init_license();
		update_option( 'date_format', 'Y/m/d' );
		set_transient( LICENSE_STATUS_TRANSIENT, 'active' );
		set_transient( LICENSE_NAME_TRANSIENT, 'Long-term service' );
		set_transient( LICENSE_EXPIRY_TRANSIENT, array( true, '2050-01-01T00:00:00.000000Z' ) );
		$links = array(
			'Version 1.0.0',
			'By <a href="https://adrianduffell.com">Adrian Duffell</a>',
		);

		// Act.
		$result = apply_filters( 'plugin_row_meta', $links, plugin_basename( PLUGIN_FILE ) );

		// Assert.
		$this->assertCount( 4, $result );
		$this->assertSame(
			'<span class="outletpro-license-expiry">Long-term service until 2050/01/01</span>',
			$result[ array_key_last( $result ) ]
		);
	}

	public function test_does_not_add_support_link_to_other_plugins(): void {
		// Arrange.
		init_license();
		$links = array(
			'Version 1.0.0',
			'By <a href="https://example.com">Another Plugin Author</a>',
			'<a href="https://example.com/plugin">Visit plugin site</a>',
		);

		// Act.
		$result = apply_filters( 'plugin_row_meta', $links, 'another-plugin/plugin.php' );

		// Assert.
		$this->assertSame( $links, $result );
	}

	public function test_adds_future_license_expiry_using_site_date_format(): void {
		// Arrange.
		init_license();
		update_option( 'date_format', 'Y/m/d' );
		set_transient( LICENSE_STATUS_TRANSIENT, 'active' );
		set_transient( LICENSE_NAME_TRANSIENT, 'Long-term service' );
		set_transient( LICENSE_EXPIRY_TRANSIENT, array( true, '2050-01-01T00:00:00.000000Z' ) );

		// Act.
		$result = apply_filters( 'plugin_row_meta', array(), plugin_basename( PLUGIN_FILE ) );

		// Assert.
		$this->assertCount( 2, $result );
		$this->assertStringContainsString( 'Support', $result[0] );
		$this->assertStringContainsString(
			'<span class="outletpro-license-expiry">Long-term service until 2050/01/01</span>',
			$result[1]
		);
	}

	public function test_adds_expired_license_expiry_highlighted_in_red(): void {
		// Arrange.
		init_license();
		update_option( 'date_format', 'Y/m/d' );
		set_transient( LICENSE_STATUS_TRANSIENT, 'expired' );
		set_transient( LICENSE_NAME_TRANSIENT, 'Long-term service' );
		set_transient( LICENSE_EXPIRY_TRANSIENT, array( true, '1997-08-29T06:14:00.000000Z' ) );

		// Act.
		$result = apply_filters( 'plugin_row_meta', array(), plugin_basename( PLUGIN_FILE ) );

		// Assert.
		$this->assertCount( 2, $result );
		$this->assertStringContainsString( 'Support', $result[0] );
		$this->assertStringContainsString(
			'<span class="outletpro-license-expiry outletpro-alert-text">Long-term service expired 1997/08/29</span>',
			$result[1]
		);
	}

	public function test_adds_non_expiring_license_message(): void {
		// Arrange.
		init_license();
		set_transient( LICENSE_STATUS_TRANSIENT, 'active' );
		set_transient( LICENSE_NAME_TRANSIENT, 'Lifetime service' );
		set_transient( LICENSE_EXPIRY_TRANSIENT, array( false ) );

		// Act.
		$result = apply_filters( 'plugin_row_meta', array(), plugin_basename( PLUGIN_FILE ) );

		// Assert.
		$this->assertCount( 2, $result );
		$this->assertStringContainsString( 'Support', $result[0] );
		$this->assertStringContainsString(
			'<span class="outletpro-license-expiry">Lifetime service (non-expiring)</span>',
			$result[1]
		);
	}

	public function test_does_not_add_license_message_when_site_is_not_activated(): void {
		// Arrange.
		init_license();
		set_transient( LICENSE_STATUS_TRANSIENT, 'none' );

		// Act.
		$result = apply_filters( 'plugin_row_meta', array(), plugin_basename( PLUGIN_FILE ) );

		// Assert.
		$this->assertCount( 1, $result );
		$this->assertStringContainsString( 'Support', $result[0] );
	}
}
