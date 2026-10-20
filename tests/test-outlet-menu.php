<?php
/**
 * Tests for the Outlet Menu block.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_blocks;
use function OutletPro\init_blocks;
use function OutletPro\init_taxonomies;
use function OutletPro\render_outlet_menu_callback;
use const OutletPro\OUTLET_PAGE_OPTION;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Outlet_Menu extends WP_UnitTestCase {

	public function test_renders_category(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		init_taxonomies();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$term = wp_insert_term( 'Clothing', 'product_cat' );

		// Act.
		$output = do_blocks( '<!-- wp:outletpro/outlet-menu ' . wp_json_encode( array( 'items' => array( $term['term_id'] ) ) ) . ' /-->' );

		// Assert.
		$this->assertStringContainsString( 'Clothing', $output );
	}

	public function test_categories_and_facets_follow_menu_order(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		init_taxonomies();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$alpha        = self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'name'     => 'Alpha',
			)
		);
		$zebra        = self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'name'     => 'Zebra',
			)
		);
		$facet        = self::factory()->term->create(
			array(
				'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
				'name'     => 'outlet-discount-50',
			)
		);
		$second_facet = self::factory()->term->create(
			array(
				'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
				'name'     => 'outlet-discount-30',
			)
		);
		update_term_meta( $facet, 'order', 2 );
		update_term_meta( $second_facet, 'order', 1 );
		update_term_meta( $alpha, 'order', 2 );
		update_term_meta( $zebra, 'order', 1 );

		// Act.
		$output = do_blocks( '<!-- wp:outletpro/outlet-menu ' . wp_json_encode( array( 'items' => array( $facet, $alpha, $second_facet, $zebra ) ) ) . ' /-->' );

		// Assert.
		$this->assertMatchesRegularExpression( '/Zebra.*Alpha.*30% off.*50% off/', $output );
	}

	public function test_renders_price(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		init_taxonomies();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		update_option( 'woocommerce_currency', 'USD' );
		$term = wp_insert_term( 'outlet-price-25', OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		$output = do_blocks( '<!-- wp:outletpro/outlet-menu ' . wp_json_encode( array( 'items' => array( $term['term_id'] ) ) ) . ' /-->' );

		// Assert.
		$this->assertStringContainsString( 'Under $25', $output );
	}

	public function test_renders_discount(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		init_taxonomies();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$term = wp_insert_term( 'outlet-discount-50', OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		$output = do_blocks( '<!-- wp:outletpro/outlet-menu ' . wp_json_encode( array( 'items' => array( $term['term_id'] ) ) ) . ' /-->' );

		// Assert.
		$this->assertStringContainsString( '50% off', $output );
	}

	public function test_missing_outlet_page_returns_null(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		delete_option( OUTLET_PAGE_OPTION );

		// Act.
		$output = render_outlet_menu_callback(
			array(
				'items'   => array(),
				'showAll' => true,
			)
		);

		// Assert.
		$this->assertNull( $output );
	}

	public function test_deleted_outlet_page_returns_null(): void {
		// Arrange.
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		wp_delete_post( $page_id, true );

		// Act.
		$output = render_outlet_menu_callback( array() );

		// Assert.
		$this->assertNull( $output );
	}

	public function test_invalid_outlet_page_option_returns_null(): void {
		// Arrange.
		update_option( OUTLET_PAGE_OPTION, 'invalid' );

		// Act.
		$output = render_outlet_menu_callback( array() );

		// Assert.
		$this->assertNull( $output );
	}

	public function test_missing_items_render_an_empty_menu(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );

		// Act.
		$output = do_blocks( '<!-- wp:outletpro/outlet-menu /-->' );

		// Assert.
		$this->assertStringContainsString( '<menu ', $output );
		$this->assertStringNotContainsString( '<li>', $output );
	}

	public function test_empty_items_render_an_empty_menu(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		init_taxonomies();
		self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'name'     => 'Unselected category',
			)
		);

		// Act.
		$output = do_blocks( '<!-- wp:outletpro/outlet-menu {"items":[]} /-->' );

		// Assert.
		$this->assertStringNotContainsString( '<a', $output );
	}

	public function test_invalid_items_return_null(): void {
		// Arrange.
		$attributes = array( 'items' => 'invalid' );

		// Act.
		$output = render_outlet_menu_callback( $attributes );

		// Assert.
		$this->assertNull( $output );
	}

	public function test_invalid_term_ids_return_null(): void {
		// Arrange.
		$attributes = array( 'items' => array( '25' ) );

		// Act.
		$output = render_outlet_menu_callback( $attributes );

		// Assert.
		$this->assertNull( $output );
	}
}
