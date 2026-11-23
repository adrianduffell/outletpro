<?php
/**
 * Test the activate function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\activate;
use function OutletPro\deinit_settings;
use function OutletPro\deinit_taxonomies;
use function OutletPro\deinit_widgets;
use function OutletPro\register_outlet_search_index_taxonomy;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use const OutletPro\ACTIVATED_AT_OPTION;
use const OutletPro\DB_VERSION;
use const OutletPro\LEGACY_OUTLET_PAGE_TEMPLATE_OPTION;
use const OutletPro\OUTLET_HERO_PATTERN_THEME_MOD;
use const OutletPro\OUTLET_HERO_WIDGET_AREA;
use const OutletPro\OUTLET_MARKDOWN_LEVELS_OPTION;
use const OutletPro\OUTLET_PAGE_OPTION;
use const OutletPro\OUTLET_PRICE_LEVELS_OPTION;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;
use const OutletPro\PLUGIN_FILE;

class Test_Activate extends WP_UnitTestCase {

	public function test_seeds_the_outlet_hero_pattern_on_activation(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		remove_theme_mod( OUTLET_HERO_PATTERN_THEME_MOD );
		self::factory()->term->create_many( 4, array( 'taxonomy' => 'product_cat' ) );

		// Act.
		activate();

		// Assert.
		$this->assertSame( 'outletpro/outlet-navigation-4', get_theme_mod( OUTLET_HERO_PATTERN_THEME_MOD ) );
	}

	public function test_does_not_publish_the_outlet_hero_on_activation(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		remove_theme_mod( OUTLET_HERO_PATTERN_THEME_MOD );

		// Act.
		activate();

		// Assert.
		$this->assertEmpty(
			get_posts(
				array(
					'post_type' => 'wp_template_part',
					'name'      => 'outlet-hero',
				)
			)
		);
	}

	public function test_seeds_the_db_version(): void {
		// Arrange.
		delete_option( 'outletpro_db_version' );

		// Act.
		activate();

		// Assert.
		$this->assertSame( DB_VERSION, get_option( 'outletpro_db_version' ) );
	}

	public function test_new_install_does_not_enable_legacy_outlet_page_template(): void {
		// Arrange.
		delete_option( 'outletpro_db_version' );
		delete_option( LEGACY_OUTLET_PAGE_TEMPLATE_OPTION );

		// Act.
		activate();
		require dirname( PLUGIN_FILE ) . '/migrate.php';

		// Assert.
		$this->assertFalse( get_option( LEGACY_OUTLET_PAGE_TEMPLATE_OPTION ) );
	}

	public function test_reactivation_enables_legacy_outlet_page_template_for_existing_installs(): void {
		// Arrange.
		update_option( 'outletpro_db_version', '2' );
		delete_option( LEGACY_OUTLET_PAGE_TEMPLATE_OPTION );

		// Act.
		activate();

		// Assert.
		$this->assertSame( 'yes', get_option( LEGACY_OUTLET_PAGE_TEMPLATE_OPTION ) );
		$this->assertSame( DB_VERSION, get_option( 'outletpro_db_version' ) );
	}

	public function test_seeds_outlet_search_indexes_on_activation(): void {
		// Arrange.
		delete_option( OUTLET_PRICE_LEVELS_OPTION );
		delete_option( OUTLET_MARKDOWN_LEVELS_OPTION );
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		WC_Helper_Product::create_simple_product( true, array( 'regular_price' => '70' ) );
		deinit_taxonomies();
		deinit_settings();

		// Act.
		activate();

		// Assert.
		$this->assertSame(
			array( 'outlet-discount-30', 'outlet-discount-50', 'outlet-discount-70', 'outlet-price-10', 'outlet-price-100', 'outlet-price-25', 'outlet-price-50' ),
			get_terms(
				array(
					'taxonomy'   => OUTLET_SEARCH_INDEX_TAXONOMY,
					'hide_empty' => false,
					'fields'     => 'slugs',
				)
			)
		);
	}

	public function test_creates_outlet_page_on_activation(): void {
		// Arrange.
		delete_option( OUTLET_PAGE_OPTION );

		// Act.
		activate();

		// Assert.
		$this->assertGreaterThan( 0, get_option( OUTLET_PAGE_OPTION ) );
	}

	public function test_seeds_activated_at_option_on_activation(): void {
		// Arrange.
		delete_option( ACTIVATED_AT_OPTION );

		// Act.
		activate();

		// Assert.
		$this->assertNotFalse( get_option( ACTIVATED_AT_OPTION ) );
	}

	public function test_block_theme_skips_hero_widget_initialization(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		remove_theme_mod( 'wp_classic_sidebars' );
		deinit_widgets();
		wp_set_sidebars_widgets( array( 'wp_inactive_widgets' => array() ) );
		$before = get_option( 'widget_block' );

		// Act.
		wp_widgets_init();
		activate();

		// Assert.
		$this->assertFalse( is_registered_sidebar( OUTLET_HERO_WIDGET_AREA ) );
		$this->assertSame( $before, get_option( 'widget_block' ) );
	}

	public function test_reactivation_seeds_an_empty_hero_area_after_widget_initialization(): void {
		// Arrange.
		switch_theme( 'storefront' );
		$GLOBALS['wp_registered_widgets'] = array();
		wp_widgets_init();
		unregister_sidebar( OUTLET_HERO_WIDGET_AREA );
		update_option( ACTIVATED_AT_OPTION, time() );
		update_option( 'widget_block', array() );
		wp_set_sidebars_widgets( array( OUTLET_HERO_WIDGET_AREA => array() ) );

		// Act.
		activate();

		// Assert.
		$this->assertCount( 1, wp_get_sidebars_widgets()[ OUTLET_HERO_WIDGET_AREA ] );
	}
}
