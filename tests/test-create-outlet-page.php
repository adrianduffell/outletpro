<?php
/**
 * Test the create_outlet_page function.
 *
 * @package WC_Outlet
 */

use function WC_Outlet\create_outlet_page;
use function WC_Outlet\run_create_outlet_page_tool;
use const WC_Outlet\OUTLET_PAGE_OPTION;

class Test_Create_Outlet_Page extends WP_UnitTestCase {

	public function test_creates_page_with_title_outlet(): void {
		// Arrange.
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

	public function test_creates_page_with_outlet_shortcode_on_classic_theme(): void {
		// Arrange.
		switch_theme( 'storefront' );
		delete_option( OUTLET_PAGE_OPTION );
		$products_per_row  = wc_get_default_products_per_row();
		$products_per_page = $products_per_row * wc_get_default_product_rows_per_page();

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
		$this->assertStringContainsString(
			sprintf(
				'[products wc_outlet="yes" paginate="yes" columns="%d" limit="%d"]',
				$products_per_page,
				$products_per_row
			),
			$pages[0]->post_content
		);
	}

	public function test_creates_page_with_product_collection_block_on_block_theme(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		delete_option( OUTLET_PAGE_OPTION );
		delete_option( 'woocommerce_default_catalog_orderby' );

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
		$this->assertStringContainsString( 'wp:woocommerce/product-collection', $pages[0]->post_content );
		$this->assertStringNotContainsString( '"taxQuery"', $pages[0]->post_content );
		$this->assertStringContainsString( '"wc_outlet":true', $pages[0]->post_content );
		$this->assertStringContainsString( '"filterable":true', $pages[0]->post_content );
		$this->assertStringContainsString( '"order":"asc"', $pages[0]->post_content );
		$this->assertStringContainsString( '"orderBy":"menu_order"', $pages[0]->post_content );
		$this->assertStringNotContainsString( '"collection":"wc-outlet/product-collection/outlet"', $pages[0]->post_content );
	}

	public function test_creates_page_with_price_desc_sort_on_block_theme_when_store_default_is_price_desc(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		delete_option( OUTLET_PAGE_OPTION );
		update_option( 'woocommerce_default_catalog_orderby', 'price-desc' );

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
		$this->assertStringContainsString( '"order":"desc"', $pages[0]->post_content );
		$this->assertStringContainsString( '"orderBy":"price"', $pages[0]->post_content );
	}

	public function test_creates_page_on_block_theme_when_canonical_term_missing(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
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
		$this->assertStringContainsString( '"wc_outlet":true', $pages[0]->post_content );
	}

	public function test_assigns_outlet_page_template_on_block_theme(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
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
		$this->assertSame( 'outlet-page', $template );
	}

	public function test_returns_success_message(): void {
		// Arrange.
		delete_option( OUTLET_PAGE_OPTION );

		// Act.
		$result = run_create_outlet_page_tool();

		// Assert.
		$this->assertSame( 'Outlet page created.', $result );
	}

	public function test_saves_page_id_in_option(): void {
		// Arrange.
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
		delete_option( OUTLET_PAGE_OPTION );
		run_create_outlet_page_tool();

		// Act.
		$result = run_create_outlet_page_tool();

		// Assert.
		$this->assertSame( 'Outlet page already exists.', $result );
	}

	public function test_creates_page_when_existing_page_is_trashed(): void {
		// Arrange.
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
