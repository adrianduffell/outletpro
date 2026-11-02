<?php
/**
 * Tests for the standalone settings migration script.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use const OutletPro\PLUGIN_FILE;
use const OutletPro\SETTINGS_VERSION;

class Test_Migrate extends WP_UnitTestCase {

	public function test_migrates_when_option_is_missing(): void {
		// Arrange.
		delete_option( 'outletpro_settings_version' );

		// Act.
		require dirname( PLUGIN_FILE ) . '/migrate.php';

		// Assert.
		$this->assertSame( SETTINGS_VERSION, get_option( 'outletpro_settings_version' ) );
	}

	public function test_migrates_when_option_value_smaller(): void {
		// Arrange.
		update_option( 'outletpro_settings_version', '0' );

		// Act.
		require dirname( PLUGIN_FILE ) . '/migrate.php';

		// Assert.
		$this->assertSame( SETTINGS_VERSION, get_option( 'outletpro_settings_version' ) );
	}

	public function test_noops_when_values_match(): void {
		// Arrange.
		update_option( 'outletpro_settings_version', SETTINGS_VERSION );

		// Act.
		require dirname( PLUGIN_FILE ) . '/migrate.php';

		// Assert.
		$this->assertSame( SETTINGS_VERSION, get_option( 'outletpro_settings_version' ) );
	}

	public function test_noops_when_option_value_greater(): void {
		// Arrange.
		update_option( 'outletpro_settings_version', '2' );

		// Act.
		require dirname( PLUGIN_FILE ) . '/migrate.php';

		// Assert.
		$this->assertSame( '2', get_option( 'outletpro_settings_version' ) );
	}
}
