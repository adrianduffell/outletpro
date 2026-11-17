<?php
/**
 * Test the activate function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\activate;
use function OutletPro\register_outlet_search_index_taxonomy;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use const OutletPro\ACTIVATED_AT_OPTION;
use const OutletPro\DB_VERSION;
use const OutletPro\OUTLET_HERO_WIDGET_AREA;
use const OutletPro\OUTLET_PAGE_OPTION;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Activate extends WP_UnitTestCase {

	public function test_seeds_the_db_version(): void {
		// Arrange.
		delete_option( 'outletpro_db_version' );

		// Act.
		activate();

		// Assert.
		$this->assertSame( DB_VERSION, get_option( 'outletpro_db_version' ) );
	}

	public function test_seeds_outlet_search_indexes_on_activation(): void {
		// Arrange.
		register_outlet_search_index_taxonomy();
		truncate_outlet_search_index_taxonomy();
		update_option( 'woocommerce_currency', 'USD' );

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

	public function test_block_theme_does_not_register_or_seed_the_hero_widget_area(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		remove_theme_mod( 'wp_classic_sidebars' );
		unregister_sidebar( OUTLET_HERO_WIDGET_AREA );
		wp_set_sidebars_widgets( array( 'wp_inactive_widgets' => array() ) );
		$before = get_option( 'widget_block' );

		// Act.
		wp_widgets_init();
		activate();

		// Assert.
		$this->assertFalse( is_registered_sidebar( OUTLET_HERO_WIDGET_AREA ) );
		$this->assertSame( $before, get_option( 'widget_block' ) );
	}

	public function test_reactivation_registers_and_seeds_an_empty_hero_area_after_widget_initialization(): void {
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
		$this->assertTrue( is_registered_sidebar( OUTLET_HERO_WIDGET_AREA ) );
		$this->assertCount( 1, wp_get_sidebars_widgets()[ OUTLET_HERO_WIDGET_AREA ] );
	}
}
