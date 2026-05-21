<?php
/**
 * Test the create_outlet_page function.
 *
 * @package WC_Outlet
 */

use function WC_Outlet\create_outlet_page;
use function WC_Outlet\run_create_outlet_page_tool;
use function WC_Outlet\seed_outlet_status_taxonomy;
use const WC_Outlet\OUTLET_PAGE_OPTION;
use const WC_Outlet\OUTLET_STATUS_CANONICAL_TERM;
use const WC_Outlet\OUTLET_STATUS_TAXONOMY;

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
		$this->assertStringContainsString( '[products wc_outlet="yes"]', $pages[0]->post_content );
	}

	public function test_creates_page_with_product_collection_block_on_block_theme(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		seed_outlet_status_taxonomy();
		$canonical_term = get_term_by( 'name', OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY );
		$this->assertInstanceOf( WP_Term::class, $canonical_term );
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
		$this->assertStringContainsString( 'wp:woocommerce/product-collection', $pages[0]->post_content );
		$this->assertStringContainsString( (string) $canonical_term->term_id, $pages[0]->post_content );
	}

	public function test_throws_runtime_exception_on_block_theme_when_canonical_term_missing(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		delete_option( OUTLET_PAGE_OPTION );

		// Expect.
		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Could not resolve the canonical outlet status term.' );

		// Act.
		create_outlet_page();
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
