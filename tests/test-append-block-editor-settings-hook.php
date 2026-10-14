<?php
/**
 * Tests for append_block_editor_settings_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\init_block_editor;
use function OutletPro\init_taxonomies;
use function OutletPro\seed_outlet_status_taxonomy;
use const OutletPro\OUTLET_PAGE_OPTION;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;
use const OutletPro\OUTLET_STATUS_CANONICAL_TERM;
use const OutletPro\OUTLET_STATUS_TAXONOMY;

class Test_Append_Block_Editor_Settings_Hook extends WP_UnitTestCase {

	public function test_settings_contain_outlet_term_id(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		init_block_editor();
		$canonical_term = get_term_by( 'name', OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY );

		// Act.
		$settings = apply_filters( 'block_editor_settings_all', array(), new WP_Block_Editor_Context() );

		// Assert.
		$this->assertArrayHasKey( 'wcOutletCanonicalTermId', $settings );
		$this->assertSame( $canonical_term->term_id, $settings['wcOutletCanonicalTermId'] );
	}

	public function test_canonical_term_setting_is_omitted_when_canonical_term_missing(): void {
		// Arrange.
		init_taxonomies();
		init_block_editor();
		$initial_settings = array( 'foo' => 'bar' );

		// Act.
		$settings = apply_filters( 'block_editor_settings_all', $initial_settings, new WP_Block_Editor_Context() );

		// Assert.
		$this->assertArrayNotHasKey( 'wcOutletCanonicalTermId', $settings );
		$this->assertArrayHasKey( 'foo', $settings );
		$this->assertSame( 'bar', $settings['foo'] );
	}

	public function test_existing_settings_are_preserved(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		init_block_editor();
		$initial_settings = array( 'foo' => 'bar' );

		// Act.
		$settings = apply_filters( 'block_editor_settings_all', $initial_settings, new WP_Block_Editor_Context() );

		// Assert.
		$this->assertArrayHasKey( 'foo', $settings );
		$this->assertSame( 'bar', $settings['foo'] );
	}

	public function test_settings_identify_block_theme(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		init_block_editor();

		// Act.
		$settings = apply_filters( 'block_editor_settings_all', array(), new WP_Block_Editor_Context() );

		// Assert.
		$this->assertArrayHasKey( 'outletproIsBlockTheme', $settings );
		$this->assertTrue( $settings['outletproIsBlockTheme'] );
	}

	public function test_settings_identify_classic_theme(): void {
		// Arrange.
		switch_theme( 'storefront' );
		init_block_editor();

		// Act.
		$settings = apply_filters( 'block_editor_settings_all', array(), new WP_Block_Editor_Context() );

		// Assert.
		$this->assertFalse( $settings['outletproIsBlockTheme'] );
	}

	public function test_menu_settings_contain_grouped_names_and_queries(): void {
		// Arrange.
		init_taxonomies();
		init_block_editor();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		update_option( 'woocommerce_currency', 'USD' );
		$category_id = self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'slug'     => 'clothing',
				'name'     => 'Clothing & Accessories',
			)
		);
		$product_id  = self::factory()->post->create(
			array(
				'post_type'   => 'product',
				'post_status' => 'publish',
			)
		);
		wp_set_object_terms( $product_id, array( $category_id ), 'product_cat' );
		wp_insert_term( 'outlet-price-50', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-price-15', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-discount-70', OUTLET_SEARCH_INDEX_TAXONOMY );
		wp_insert_term( 'outlet-discount-45', OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		$settings = apply_filters( 'block_editor_settings_all', array(), new WP_Block_Editor_Context() );

		// Assert.
		$this->assertSame( get_permalink( $page_id ), $settings['outletproMenuPermalink'] );
		$this->assertSame( array( 'All', '' ), $settings['outletproMenuItems']['categories'][0] );
		$this->assertContains( array( 'Clothing & Accessories', 'outlet_category=clothing' ), $settings['outletproMenuItems']['categories'] );
		$this->assertSame( array( 'Under $15', 'outlet_price=15' ), $settings['outletproMenuItems']['prices'][0] );
		$this->assertSame( array( 'Under $50', 'outlet_price=50' ), $settings['outletproMenuItems']['prices'][1] );
		$this->assertSame( array( '45% off', 'outlet_discount=45' ), $settings['outletproMenuItems']['discounts'][0] );
		$this->assertSame( array( '70% off', 'outlet_discount=70' ), $settings['outletproMenuItems']['discounts'][1] );
	}

	public function test_menu_settings_are_empty_without_an_outlet_page(): void {
		// Arrange.
		init_block_editor();
		delete_option( OUTLET_PAGE_OPTION );

		// Act.
		$settings = apply_filters( 'block_editor_settings_all', array(), new WP_Block_Editor_Context() );

		// Assert.
		$this->assertSame(
			array(
				'categories' => array(),
				'prices'     => array(),
				'discounts'  => array(),
			),
			$settings['outletproMenuItems']
		);
	}
}
