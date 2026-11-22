<?php
/**
 * Tests for the standalone database migration script.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\init_taxonomies;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use const OutletPro\DB_VERSION;
use const OutletPro\LEGACY_OUTLET_PAGE_TEMPLATE_OPTION;
use const OutletPro\OUTLET_MARKDOWN_LEVELS_OPTION;
use const OutletPro\OUTLET_PRICE_LEVELS_OPTION;
use const OutletPro\PLUGIN_FILE;
use const OutletPro\SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD;

class Test_Migrate extends WP_UnitTestCase {

	public function test_ignores_price_facets_in_other_taxonomies(): void {
		// Arrange.
		init_taxonomies();
		update_option( 'outletpro_db_version', '2' );
		delete_option( OUTLET_PRICE_LEVELS_OPTION );
		truncate_outlet_search_index_taxonomy();
		wp_insert_term( 'outlet-price-1000', 'category' );

		// Act.
		require dirname( PLUGIN_FILE ) . '/migrate.php';

		// Assert.
		$this->assertSame( array(), get_option( OUTLET_PRICE_LEVELS_OPTION, null ) );
	}

	public function test_migrates_empty_price_levels(): void {
		// Arrange.
		init_taxonomies();
		update_option( 'outletpro_db_version', '2' );
		delete_option( OUTLET_PRICE_LEVELS_OPTION );
		truncate_outlet_search_index_taxonomy();

		// Act.
		require dirname( PLUGIN_FILE ) . '/migrate.php';

		// Assert.
		$this->assertSame( array(), get_option( OUTLET_PRICE_LEVELS_OPTION, null ) );
	}

	public function test_migrates_default_markdown_levels(): void {
		// Arrange.
		update_option( 'outletpro_db_version', '2' );
		delete_option( OUTLET_MARKDOWN_LEVELS_OPTION );

		// Act.
		require dirname( PLUGIN_FILE ) . '/migrate.php';

		// Assert.
		$this->assertSame( array( 30, 50, 70 ), get_option( OUTLET_MARKDOWN_LEVELS_OPTION ) );
	}

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
		$this->assertSame( 'fine', get_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD ) );
	}

	public function test_enables_legacy_outlet_page_template_for_existing_installs(): void {
		// Arrange.
		update_option( 'outletpro_db_version', '2' );
		delete_option( LEGACY_OUTLET_PAGE_TEMPLATE_OPTION );

		// Act.
		require dirname( PLUGIN_FILE ) . '/migrate.php';

		// Assert.
		$this->assertSame( 'yes', get_option( LEGACY_OUTLET_PAGE_TEMPLATE_OPTION ) );
		$this->assertSame( DB_VERSION, get_option( 'outletpro_db_version' ) );
	}

	public function test_preserves_disabled_legacy_outlet_page_template_on_migration(): void {
		// Arrange.
		update_option( 'outletpro_db_version', '2' );
		update_option( LEGACY_OUTLET_PAGE_TEMPLATE_OPTION, 'no' );

		// Act.
		require dirname( PLUGIN_FILE ) . '/migrate.php';

		// Assert.
		$this->assertSame( 'no', get_option( LEGACY_OUTLET_PAGE_TEMPLATE_OPTION ) );
	}
}
