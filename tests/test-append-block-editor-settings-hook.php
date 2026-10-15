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

	public function test_menu_settings_contain_category_options(): void {
		// Arrange.
		init_taxonomies();
		init_block_editor();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$term = wp_insert_term( 'Clothing', 'product_cat' );

		// Act.
		$settings = apply_filters( 'block_editor_settings_all', array(), new WP_Block_Editor_Context() );

		// Assert.
		$this->assertContains(
			array(
				'id'    => $term['term_id'],
				'name'  => 'Clothing',
				'query' => 'outlet_category=clothing',
			),
			$settings['outletproMenuItems']['categories']
		);
	}

	public function test_menu_settings_contain_price_options(): void {
		// Arrange.
		init_taxonomies();
		init_block_editor();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		update_option( 'woocommerce_currency', 'USD' );
		$term = wp_insert_term( 'outlet-price-15', OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		$settings = apply_filters( 'block_editor_settings_all', array(), new WP_Block_Editor_Context() );

		// Assert.
		$this->assertSame(
			array(
				'id'    => $term['term_id'],
				'name'  => 'Under $15',
				'query' => 'outlet_price=15',
			),
			$settings['outletproMenuItems']['prices'][0]
		);
	}

	public function test_menu_settings_contain_discount_options(): void {
		// Arrange.
		init_taxonomies();
		init_block_editor();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$term = wp_insert_term( 'outlet-discount-45', OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		$settings = apply_filters( 'block_editor_settings_all', array(), new WP_Block_Editor_Context() );

		// Assert.
		$this->assertSame(
			array(
				'id'    => $term['term_id'],
				'name'  => '45% off',
				'query' => 'outlet_discount=45',
			),
			$settings['outletproMenuItems']['discounts'][0]
		);
	}

	public function test_menu_settings_contain_the_outlet_permalink(): void {
		// Arrange.
		init_block_editor();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );

		// Act.
		$settings = apply_filters( 'block_editor_settings_all', array(), new WP_Block_Editor_Context() );

		// Assert.
		$this->assertSame( get_permalink( $page_id ), $settings['outletproMenuPermalink'] );
	}

	public function test_menu_settings_include_empty_top_level_categories(): void {
		// Arrange.
		init_taxonomies();
		init_block_editor();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$category_id = self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'slug'     => 'empty-category',
				'name'     => 'Empty category',
			)
		);
		self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'slug'     => 'empty-child',
				'name'     => 'Empty child',
				'parent'   => $category_id,
			)
		);

		// Act.
		$settings = apply_filters( 'block_editor_settings_all', array(), new WP_Block_Editor_Context() );

		// Assert.
		$this->assertContains(
			array(
				'id'    => $category_id,
				'name'  => 'Empty category',
				'query' => 'outlet_category=empty-category',
			),
			$settings['outletproMenuItems']['categories']
		);
		$this->assertNotContains( 'outlet_category=empty-child', array_column( $settings['outletproMenuItems']['categories'], 'query' ) );
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
