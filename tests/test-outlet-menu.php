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

	public function test_renders_selected_term_ids_with_current_labels_and_queries(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		init_taxonomies();
		update_option( 'woocommerce_currency', 'USD' );
		$items = array(
			self::factory()->term->create(
				array(
					'taxonomy' => 'product_cat',
					'name'     => 'Clothing & Accessories',
					'slug'     => 'clothing',
				)
			),
			self::factory()->term->create(
				array(
					'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
					'slug'     => 'outlet-price-25',
				)
			),
			self::factory()->term->create(
				array(
					'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
					'slug'     => 'outlet-discount-50',
				)
			),
		);

		// Act.
		$output = do_blocks( '<!-- wp:outletpro/outlet-menu ' . wp_json_encode( array( 'items' => $items ) ) . ' /-->' );

		// Assert.
		$this->assertStringContainsString( '<a href="' . esc_url( add_query_arg( 'outlet_category', 'clothing', get_permalink( $page_id ) ) ) . '" style="text-decoration:none;">Clothing &amp; Accessories</a>', $output );
		$this->assertStringContainsString( '<a href="' . esc_url( add_query_arg( 'outlet_price', 25, get_permalink( $page_id ) ) ) . '" style="text-decoration:none;">Under $25</a>', $output );
		$this->assertStringContainsString( '<a href="' . esc_url( add_query_arg( 'outlet_discount', 50, get_permalink( $page_id ) ) ) . '" style="text-decoration:none;">50% off</a>', $output );
	}

	public function test_selected_terms_use_the_current_outlet_permalink(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$old_page = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$new_page = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $old_page );
		init_taxonomies();
		$term_id = self::factory()->term->create(
			array(
				'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
				'slug'     => 'outlet-discount-30',
			)
		);
		$content = '<!-- wp:outletpro/outlet-menu ' . wp_json_encode(
			array(
				'items'      => array( $term_id ),
				'includeAll' => true,
			)
		) . ' /-->';
		update_option( OUTLET_PAGE_OPTION, $new_page );

		// Act.
		$output = do_blocks( $content );

		// Assert.
		$this->assertStringContainsString( '<a href="' . esc_url( get_permalink( $new_page ) ) . '" style="text-decoration:none;">All</a>', $output );
		$this->assertStringContainsString( esc_url( add_query_arg( 'outlet_discount', 30, get_permalink( $new_page ) ) ), $output );
		$this->assertStringNotContainsString( esc_url( get_permalink( $old_page ) ), $output );
	}

	public function test_missing_outlet_page_returns_null(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		delete_option( OUTLET_PAGE_OPTION );

		// Act.
		$output = render_outlet_menu_callback(
			array(
				'items'      => array(),
				'includeAll' => true,
			)
		);

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

	public function test_unrelated_taxonomies_are_omitted(): void {
		// Arrange.
		init_taxonomies();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$tag_id = self::factory()->term->create(
			array(
				'taxonomy' => 'product_tag',
				'name'     => 'Tag',
			)
		);

		// Act.
		$output = render_outlet_menu_callback( array( 'items' => array( $tag_id ) ) );

		// Assert.
		$this->assertStringNotContainsString( '<li>', $output );
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
