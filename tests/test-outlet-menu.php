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
use function OutletPro\register_block_styles;
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
		$this->assertStringContainsString( 'is-layout-flex', $output );
		$this->assertStringContainsString( 'padding-top:0', $output );
		$this->assertStringContainsString( 'padding-right:0', $output );
		$this->assertStringContainsString( 'padding-bottom:0', $output );
		$this->assertStringContainsString( 'padding-left:0', $output );
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

	public function test_category_name_and_slug_changes_are_reflected_without_resaving(): void {
		// Arrange.
		init_taxonomies();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$term_id    = self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'name'     => 'Old name',
				'slug'     => 'old-slug',
			)
		);
		$attributes = array( 'items' => array( $term_id ) );
		wp_update_term(
			$term_id,
			'product_cat',
			array(
				'name' => 'Home & Garden',
				'slug' => 'home-garden',
			)
		);

		// Act.
		$output = render_outlet_menu_callback( $attributes );

		// Assert.
		$this->assertStringContainsString( '<a href="' . esc_url( add_query_arg( 'outlet_category', 'home-garden', get_permalink( $page_id ) ) ) . '">Home &amp; Garden</a>', $output );
		$this->assertStringNotContainsString( 'Old name', $output );
		$this->assertStringNotContainsString( 'old-slug', $output );
	}

	public function test_tier_slug_changes_update_the_label_and_query(): void {
		// Arrange.
		init_taxonomies();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		update_option( 'woocommerce_currency', 'USD' );
		$term_id    = self::factory()->term->create(
			array(
				'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
				'slug'     => 'outlet-price-25',
			)
		);
		$attributes = array( 'items' => array( $term_id ) );
		wp_update_term( $term_id, OUTLET_SEARCH_INDEX_TAXONOMY, array( 'slug' => 'outlet-price-50' ) );

		// Act.
		$output = render_outlet_menu_callback( $attributes );

		// Assert.
		$this->assertStringContainsString( '<a href="' . esc_url( add_query_arg( 'outlet_price', 50, get_permalink( $page_id ) ) ) . '">Under $50</a>', $output );
		$this->assertStringNotContainsString( 'outlet_price=25', $output );
	}

	public function test_deleted_terms_are_omitted_without_resaving(): void {
		// Arrange.
		init_taxonomies();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$category_id = self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'name'     => 'Deleted category',
			)
		);
		$tier_id     = self::factory()->term->create(
			array(
				'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
				'slug'     => 'outlet-discount-30',
			)
		);
		$attributes  = array(
			'items'      => array( $category_id, $tier_id ),
			'includeAll' => true,
		);
		wp_delete_term( $category_id, 'product_cat' );
		wp_delete_term( $tier_id, OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		$output = render_outlet_menu_callback( $attributes );

		// Assert.
		$this->assertSame( 1, substr_count( $output, '<li>' ) );
		$this->assertStringContainsString( '>All</a>', $output );
		$this->assertStringNotContainsString( 'Deleted category', $output );
		$this->assertStringNotContainsString( 'outlet_discount', $output );
	}

	public function test_terms_render_in_selected_order(): void {
		// Arrange.
		init_taxonomies();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$first_id  = self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'name'     => 'Zebra',
			)
		);
		$second_id = self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'name'     => 'Apple',
			)
		);

		// Act.
		$output = render_outlet_menu_callback( array( 'items' => array( $first_id, $second_id ) ) );

		// Assert.
		$this->assertLessThan( strpos( $output, '>Apple</a>' ), strpos( $output, '>Zebra</a>' ) );
		$this->assertStringNotContainsString( '>All</a>', $output );
	}

	public function test_unrelated_taxonomies_and_index_terms_are_omitted(): void {
		// Arrange.
		init_taxonomies();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$tag_id   = self::factory()->term->create(
			array(
				'taxonomy' => 'product_tag',
				'name'     => 'Tag',
			)
		);
		$index_id = self::factory()->term->create(
			array(
				'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
				'slug'     => 'unrelated-index',
			)
		);

		// Act.
		$output = render_outlet_menu_callback( array( 'items' => array( $tag_id, $index_id ) ) );

		// Assert.
		$this->assertStringNotContainsString( '<li>', $output );
	}

	public function test_term_query_errors_return_null(): void {
		// Arrange.
		init_taxonomies();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$term_id    = self::factory()->term->create( array( 'taxonomy' => 'product_cat' ) );
		$fail_query = static fn() => new WP_Error( 'query_failed' );
		add_filter( 'get_terms', $fail_query );

		// Act.
		$output = render_outlet_menu_callback( array( 'items' => array( $term_id ) ) );
		remove_filter( 'get_terms', $fail_query );

		// Assert.
		$this->assertNull( $output );
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

	public function test_menu_supports_native_styles_without_metadata_stylesheets(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();

		// Act.
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'outletpro/outlet-menu' );

		// Assert.
		$this->assertSame( array(), $block_type->style_handles );
		$this->assertTrue( $block_type->supports['typography']['__experimentalTextDecoration'] );
	}

	public function test_menu_does_not_enqueue_a_stylesheet(): void {
		// Arrange.
		wp_dequeue_style( 'outletpro-menu-block' );
		wp_deregister_style( 'outletpro-menu-block' );
		register_block_styles();

		// Act.
		do_action( 'wp_enqueue_scripts' );
		apply_filters( 'render_block', '', array( 'blockName' => 'outletpro/outlet-menu' ), new WP_Block( array( 'blockName' => 'outletpro/outlet-menu' ) ) );

		// Assert.
		$this->assertFalse( wp_style_is( 'outletpro-menu-block', 'registered' ) );
	}

	public function test_gutenberg_underline_setting_is_applied_to_links(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );

		// Act.
		$output = do_blocks( '<!-- wp:outletpro/outlet-menu {"includeAll":true,"style":{"typography":{"textDecoration":"underline"}}} /-->' );

		// Assert.
		$this->assertMatchesRegularExpression( '/<a[^>]+style="text-decoration:underline;?"[^>]*>All<\/a>/', $output );
	}

	public function test_gutenberg_none_setting_is_applied_to_links(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );

		// Act.
		$output = do_blocks( '<!-- wp:outletpro/outlet-menu {"includeAll":true,"style":{"typography":{"textDecoration":"none"}}} /-->' );

		// Assert.
		$this->assertMatchesRegularExpression( '/<a[^>]+style="text-decoration:none;?"[^>]*>All<\/a>/', $output );
	}

	public function test_menu_applies_native_block_styles(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );

		// Act.
		$output = do_blocks( '<!-- wp:outletpro/outlet-menu {"style":{"spacing":{"blockGap":"2em","padding":{"top":"2em"}},"typography":{"fontSize":"24px"}}} /-->' );

		// Assert.
		$this->assertStringContainsString( 'wp-block-outletpro-outlet-menu', $output );
		$this->assertStringContainsString( 'padding-top:2em', $output );
		$this->assertStringContainsString( 'font-size:24px', $output );
	}
}
