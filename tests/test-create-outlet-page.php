<?php
/**
 * Test the create_outlet_page function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\create_outlet_page;
use function OutletPro\deinit_patterns;
use function OutletPro\get_outlet_page_id;
use function OutletPro\init_patterns;
use function OutletPro\init_taxonomies;
use function OutletPro\run_create_outlet_page_tool;
use const OutletPro\OUTLET_PAGE_OPTION;

class Test_Create_Outlet_Page extends WP_UnitTestCase {

	public function test_creates_page_with_title_outlet(): void {
		// Arrange.
		init_taxonomies();
		deinit_patterns();
		init_patterns();
		delete_option( OUTLET_PAGE_OPTION );

		// Act.
		create_outlet_page();

		// Assert.
		$pages = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => 'draft',
				'name'        => 'outlet',
			)
		);
		$this->assertNotEmpty( $pages );
		$this->assertSame( 'Outlet', $pages[0]->post_title );
	}

	public function test_creates_page_with_slug_outlet(): void {
		// Arrange.
		init_taxonomies();
		deinit_patterns();
		init_patterns();
		delete_option( OUTLET_PAGE_OPTION );

		// Act.
		create_outlet_page();

		// Assert.
		$pages = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => 'draft',
				'name'        => 'outlet',
			)
		);
		$this->assertNotEmpty( $pages );
		$this->assertSame( 'outlet', $pages[0]->post_name );
	}

	public function test_creates_page_with_draft_status(): void {
		// Arrange.
		init_taxonomies();
		deinit_patterns();
		init_patterns();
		delete_option( OUTLET_PAGE_OPTION );

		// Act.
		create_outlet_page();

		// Assert.
		$pages = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => 'draft',
				'name'        => 'outlet',
			)
		);
		$this->assertNotEmpty( $pages );
		$this->assertSame( 'draft', $pages[0]->post_status );
	}

	public function test_creates_navigation_without_embedded_catalog_on_classic_theme(): void {
		// Arrange.
		init_taxonomies();
		deinit_patterns();
		init_patterns();
		switch_theme( 'storefront' );
		delete_option( OUTLET_PAGE_OPTION );

		// Act.
		create_outlet_page();

		// Assert.
		$page = get_post( get_outlet_page_id() );
		$this->assertStringContainsString( '"patternName":"outletpro/outlet-navigation-', $page->post_content );
		$this->assertStringNotContainsString( 'wp:woocommerce/product-collection', $page->post_content );
		$this->assertStringNotContainsString( '[products', $page->post_content );
		$this->assertStringNotContainsString( 'outletpro/outlet-sort-filter', $page->post_content );
	}

	public function test_creates_navigation_without_embedded_catalog_on_block_theme(): void {
		// Arrange.
		init_taxonomies();
		deinit_patterns();
		init_patterns();
		switch_theme( 'twentytwentyfive' );
		$source = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'archive-product',
				'post_content' => '<!-- wp:query-title {"type":"archive"} /-->',
			)
		);
		wp_set_object_terms( $source, get_stylesheet(), 'wp_theme' );
		delete_option( OUTLET_PAGE_OPTION );

		// Act.
		create_outlet_page();

		// Assert.
		$page = get_post( get_outlet_page_id() );
		$this->assertStringContainsString( '"patternName":"outletpro/outlet-navigation-', $page->post_content );
		$this->assertStringNotContainsString( 'wp:woocommerce/product-collection', $page->post_content );
		$this->assertStringNotContainsString( '[products', $page->post_content );
		$this->assertStringNotContainsString( 'outletpro/outlet-sort-filter', $page->post_content );
	}

	public function test_selects_navigation_with_1_top_level_category(): void {
		// Arrange.
		init_taxonomies();
		deinit_patterns();
		init_patterns();
		delete_option( OUTLET_PAGE_OPTION );
		$category_count = wp_count_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => 0,
				'hide_empty' => false,
			)
		);
		self::factory()->term->create_many( 1 - $category_count, array( 'taxonomy' => 'product_cat' ) );

		// Act.
		create_outlet_page();
		$blocks = parse_blocks( get_post_field( 'post_content', get_option( OUTLET_PAGE_OPTION ) ) );

		// Assert.
		$this->assertSame( 'outletpro/outlet-navigation-price-tiers', $blocks[0]['attrs']['metadata']['patternName'] );
	}

	public function test_selects_navigation_with_2_top_level_categories(): void {
		// Arrange.
		init_taxonomies();
		deinit_patterns();
		init_patterns();
		delete_option( OUTLET_PAGE_OPTION );
		$category_count = wp_count_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => 0,
				'hide_empty' => false,
			)
		);
		self::factory()->term->create_many( 2 - $category_count, array( 'taxonomy' => 'product_cat' ) );

		// Act.
		create_outlet_page();
		$blocks = parse_blocks( get_post_field( 'post_content', get_option( OUTLET_PAGE_OPTION ) ) );

		// Assert.
		$this->assertSame( 'outletpro/outlet-navigation-price-tiers', $blocks[0]['attrs']['metadata']['patternName'] );
	}

	public function test_selects_navigation_with_3_top_level_categories(): void {
		// Arrange.
		init_taxonomies();
		deinit_patterns();
		init_patterns();
		delete_option( OUTLET_PAGE_OPTION );
		$category_count = wp_count_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => 0,
				'hide_empty' => false,
			)
		);
		$category_ids   = self::factory()->term->create_many( 3 - $category_count, array( 'taxonomy' => 'product_cat' ) );
		self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'parent'   => $category_ids[0],
			)
		);

		// Act.
		create_outlet_page();
		$blocks = parse_blocks( get_post_field( 'post_content', get_option( OUTLET_PAGE_OPTION ) ) );

		// Assert.
		$this->assertSame( 'outletpro/outlet-navigation-3', $blocks[0]['attrs']['metadata']['patternName'] );
	}

	public function test_selects_navigation_with_4_top_level_categories(): void {
		// Arrange.
		init_taxonomies();
		deinit_patterns();
		init_patterns();
		delete_option( OUTLET_PAGE_OPTION );
		$category_count = wp_count_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => 0,
				'hide_empty' => false,
			)
		);
		self::factory()->term->create_many( 4 - $category_count, array( 'taxonomy' => 'product_cat' ) );

		// Act.
		create_outlet_page();
		$blocks = parse_blocks( get_post_field( 'post_content', get_option( OUTLET_PAGE_OPTION ) ) );

		// Assert.
		$this->assertSame( 'outletpro/outlet-navigation-4', $blocks[0]['attrs']['metadata']['patternName'] );
	}

	public function test_selects_navigation_with_5_top_level_categories(): void {
		// Arrange.
		init_taxonomies();
		deinit_patterns();
		init_patterns();
		delete_option( OUTLET_PAGE_OPTION );
		$category_count = wp_count_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => 0,
				'hide_empty' => false,
			)
		);
		self::factory()->term->create_many( 5 - $category_count, array( 'taxonomy' => 'product_cat' ) );

		// Act.
		create_outlet_page();
		$blocks = parse_blocks( get_post_field( 'post_content', get_option( OUTLET_PAGE_OPTION ) ) );

		// Assert.
		$this->assertSame( 'outletpro/outlet-navigation-3', $blocks[0]['attrs']['metadata']['patternName'] );
	}

	public function test_selects_navigation_with_6_top_level_categories(): void {
		// Arrange.
		init_taxonomies();
		deinit_patterns();
		init_patterns();
		delete_option( OUTLET_PAGE_OPTION );
		$category_count = wp_count_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => 0,
				'hide_empty' => false,
			)
		);
		self::factory()->term->create_many( 6 - $category_count, array( 'taxonomy' => 'product_cat' ) );

		// Act.
		create_outlet_page();
		$blocks = parse_blocks( get_post_field( 'post_content', get_option( OUTLET_PAGE_OPTION ) ) );

		// Assert.
		$this->assertSame( 'outletpro/outlet-navigation-6', $blocks[0]['attrs']['metadata']['patternName'] );
	}

	public function test_selects_navigation_with_7_top_level_categories(): void {
		// Arrange.
		init_taxonomies();
		deinit_patterns();
		init_patterns();
		delete_option( OUTLET_PAGE_OPTION );
		$category_count = wp_count_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => 0,
				'hide_empty' => false,
			)
		);
		self::factory()->term->create_many( 7 - $category_count, array( 'taxonomy' => 'product_cat' ) );

		// Act.
		create_outlet_page();
		$blocks = parse_blocks( get_post_field( 'post_content', get_option( OUTLET_PAGE_OPTION ) ) );

		// Assert.
		$this->assertSame( 'outletpro/outlet-navigation-3', $blocks[0]['attrs']['metadata']['patternName'] );
	}

	public function test_selects_navigation_with_8_top_level_categories(): void {
		// Arrange.
		init_taxonomies();
		deinit_patterns();
		init_patterns();
		delete_option( OUTLET_PAGE_OPTION );
		$category_count = wp_count_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => 0,
				'hide_empty' => false,
			)
		);
		self::factory()->term->create_many( 8 - $category_count, array( 'taxonomy' => 'product_cat' ) );

		// Act.
		create_outlet_page();
		$blocks = parse_blocks( get_post_field( 'post_content', get_option( OUTLET_PAGE_OPTION ) ) );

		// Assert.
		$this->assertSame( 'outletpro/outlet-navigation-3', $blocks[0]['attrs']['metadata']['patternName'] );
	}

	public function test_creates_page_on_block_theme_when_canonical_term_missing(): void {
		// Arrange.
		init_taxonomies();
		deinit_patterns();
		init_patterns();
		switch_theme( 'twentytwentyfive' );
		$source = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'archive-product',
				'post_content' => '<!-- wp:query-title {"type":"archive"} /-->',
			)
		);
		wp_set_object_terms( $source, get_stylesheet(), 'wp_theme' );
		delete_option( OUTLET_PAGE_OPTION );

		// Act.
		create_outlet_page();

		// Assert.
		$pages = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => 'draft',
				'name'        => 'outlet',
			)
		);
		$this->assertNotEmpty( $pages );
		$this->assertStringContainsString( '"patternName":"outletpro/outlet-navigation-', $pages[0]->post_content );
	}

	public function test_assigns_outlet_page_template_on_block_theme(): void {
		// Arrange.
		init_taxonomies();
		deinit_patterns();
		init_patterns();
		switch_theme( 'twentytwentyfive' );
		$source = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'archive-product',
				'post_content' => '<!-- wp:query-title {"type":"archive"} /-->',
			)
		);
		wp_set_object_terms( $source, get_stylesheet(), 'wp_theme' );
		delete_option( OUTLET_PAGE_OPTION );

		// Act.
		create_outlet_page();

		// Assert.
		$pages = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => 'draft',
				'name'        => 'outlet',
			)
		);
		$this->assertNotEmpty( $pages );
		$template = get_post_meta( $pages[0]->ID, '_wp_page_template', true );
		$this->assertSame( 'outlet-catalog', $template );
	}

	public function test_returns_success_message(): void {
		// Arrange.
		init_taxonomies();
		deinit_patterns();
		init_patterns();
		delete_option( OUTLET_PAGE_OPTION );

		// Act.
		$result = run_create_outlet_page_tool();

		// Assert.
		$this->assertSame( 'Outlet page created.', $result );
	}

	public function test_saves_page_id_in_option(): void {
		// Arrange.
		init_taxonomies();
		deinit_patterns();
		init_patterns();
		delete_option( OUTLET_PAGE_OPTION );

		// Act.
		create_outlet_page();

		// Assert.
		$pages = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => 'draft',
				'name'        => 'outlet',
			)
		);
		$this->assertNotEmpty( $pages );
		$this->assertSame( $pages[0]->ID, get_option( OUTLET_PAGE_OPTION ) );
	}

	public function test_does_not_create_duplicate_page_when_page_already_exists(): void {
		// Arrange.
		init_taxonomies();
		deinit_patterns();
		init_patterns();
		delete_option( OUTLET_PAGE_OPTION );
		create_outlet_page();
		$original_page_id = get_option( OUTLET_PAGE_OPTION );

		// Act.
		create_outlet_page();

		// Assert.
		$page_id = get_option( OUTLET_PAGE_OPTION );
		$this->assertSame( $original_page_id, $page_id );
	}

	public function test_returns_already_exists_message_when_page_already_exists(): void {
		// Arrange.
		init_taxonomies();
		deinit_patterns();
		init_patterns();
		delete_option( OUTLET_PAGE_OPTION );
		run_create_outlet_page_tool();

		// Act.
		$result = run_create_outlet_page_tool();

		// Assert.
		$this->assertSame( 'Outlet page already exists.', $result );
	}

	public function test_creates_page_when_existing_page_is_trashed(): void {
		// Arrange.
		init_taxonomies();
		deinit_patterns();
		init_patterns();
		delete_option( OUTLET_PAGE_OPTION );
		$trashed_id = wp_insert_post(
			array(
				'post_title'  => 'Clearance',
				'post_type'   => 'page',
				'post_status' => 'trash',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $trashed_id );

		// Act.
		$result = run_create_outlet_page_tool();

		// Assert.
		$this->assertSame( 'Outlet page created.', $result );
	}

	public function test_returns_could_not_be_created_message_when_option_is_corrupted(): void {
		// Arrange.
		update_option( OUTLET_PAGE_OPTION, 'not-an-int' );

		// Act.
		$result = run_create_outlet_page_tool();

		// Assert.
		$this->assertSame( 'Outlet page could not be created.', $result );
	}

	public function test_throws_runtime_exception_when_option_is_corrupted(): void {
		// Arrange.
		update_option( OUTLET_PAGE_OPTION, 'not-an-int' );

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		create_outlet_page();
	}
}
