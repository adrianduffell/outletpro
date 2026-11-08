<?php
/**
 * Tests for the standalone database migration script.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use const OutletPro\DB_VERSION;
use const OutletPro\PLUGIN_FILE;
use const OutletPro\SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD;

class Test_Migrate extends WP_UnitTestCase {

	public function test_migrates_when_option_is_missing(): void {
		// Arrange.
		delete_option( 'outletpro_db_version' );

		// Act.
		require dirname( PLUGIN_FILE ) . '/migrate.php';

		// Assert.
		$this->assertSame( DB_VERSION, get_option( 'outletpro_db_version' ) );
	}

	public function test_migrates_when_option_value_smaller(): void {
		// Arrange.
		update_option( 'outletpro_db_version', '0' );

		// Act.
		require dirname( PLUGIN_FILE ) . '/migrate.php';

		// Assert.
		$this->assertSame( DB_VERSION, get_option( 'outletpro_db_version' ) );
	}

	public function test_noops_when_values_match(): void {
		// Arrange.
		update_option( 'outletpro_db_version', DB_VERSION );

		// Act.
		require dirname( PLUGIN_FILE ) . '/migrate.php';

		// Assert.
		$this->assertSame( DB_VERSION, get_option( 'outletpro_db_version' ) );
	}

	public function test_noops_when_option_value_greater(): void {
		// Arrange.
		update_option( 'outletpro_db_version', '9999' );

		// Act.
		require dirname( PLUGIN_FILE ) . '/migrate.php';

		// Assert.
		$this->assertSame( '9999', get_option( 'outletpro_db_version' ) );
	}

	public function test_migrates_single_product_message_block_style(): void {
		// Arrange.
		update_option( 'outletpro_db_version', '2' );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD );

		// Act.
		require dirname( PLUGIN_FILE ) . '/migrate.php';

		// Assert.
		$this->assertSame( '', get_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD ) );
	}
}
