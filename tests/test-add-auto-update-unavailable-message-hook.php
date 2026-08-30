<?php
/**
 * Tests for add_auto_update_unavailable_label_hook().
 *
 * @package OutletPro
 * @group license
 * @copyright © 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\init_license;
use const OutletPro\LICENSE_STATUS_TRANSIENT;
use const OutletPro\PLUGIN_FILE;

class Test_Add_Auto_Update_Unavailable_Label_Hook extends WP_UnitTestCase {

	public function test_adds_message_when_auto_updates_are_unavailable(): void {
		// Arrange.
		init_license();
		set_transient( LICENSE_STATUS_TRANSIENT, 'none', WEEK_IN_SECONDS );
		$html = '<span class="label"></span>';

		// Act.
		$result = apply_filters(
			'plugin_auto_update_setting_html',
			$html,
			plugin_basename( PLUGIN_FILE ),
			array( 'update-supported' => false )
		);

		// Assert.
		$this->assertSame(
			'<span class="label">Auto-updates unavailable</span>',
			$result
		);
	}

	public function test_preserves_empty_setting_when_license_is_active(): void {
		// Arrange.
		init_license();
		set_transient( LICENSE_STATUS_TRANSIENT, 'active', WEEK_IN_SECONDS );
		$html = '<span class="label"></span>';

		// Act.
		$result = apply_filters(
			'plugin_auto_update_setting_html',
			$html,
			plugin_basename( PLUGIN_FILE ),
			array( 'update-supported' => false )
		);

		// Assert.
		$this->assertSame( $html, $result );
	}

	public function test_preserves_empty_setting_when_license_status_errors(): void {
		// Arrange.
		init_license();
		set_transient( LICENSE_STATUS_TRANSIENT, 'error', DAY_IN_SECONDS );
		$html = '<span class="label"></span>';

		// Act.
		$result = apply_filters(
			'plugin_auto_update_setting_html',
			$html,
			plugin_basename( PLUGIN_FILE ),
			array( 'update-supported' => false )
		);

		// Assert.
		$this->assertSame( $html, $result );
	}

	public function test_preserves_setting_when_auto_updates_are_supported(): void {
		// Arrange.
		init_license();
		$html = '<a class="toggle-auto-update">Enable auto-updates</a>';

		// Act.
		$result = apply_filters(
			'plugin_auto_update_setting_html',
			$html,
			plugin_basename( PLUGIN_FILE ),
			array( 'update-supported' => true )
		);

		// Assert.
		$this->assertSame( $html, $result );
	}

	public function test_preserves_forced_auto_update_status(): void {
		// Arrange.
		init_license();
		$html = '<span class="label">Auto-updates enabled</span>';

		// Act.
		$result = apply_filters(
			'plugin_auto_update_setting_html',
			$html,
			plugin_basename( PLUGIN_FILE ),
			array(
				'auto-update-forced' => true,
				'update-supported'   => false,
			)
		);

		// Assert.
		$this->assertSame( $html, $result );
	}

	public function test_preserves_forced_disabled_auto_update_status(): void {
		// Arrange.
		init_license();
		$html = '<span class="label">Auto-updates disabled</span>';

		// Act.
		$result = apply_filters(
			'plugin_auto_update_setting_html',
			$html,
			plugin_basename( PLUGIN_FILE ),
			array(
				'auto-update-forced' => false,
				'update-supported'   => false,
			)
		);

		// Assert.
		$this->assertSame( $html, $result );
	}

	public function test_does_not_change_other_plugins(): void {
		// Arrange.
		init_license();
		$html = '<span class="label"></span>';

		// Act.
		$result = apply_filters(
			'plugin_auto_update_setting_html',
			$html,
			'another-plugin/plugin.php',
			array( 'update-supported' => false )
		);

		// Assert.
		$this->assertSame( $html, $result );
	}
}
