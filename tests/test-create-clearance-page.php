<?php
/**
 * Test the create_clearance_page function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\create_clearance_page;
use function WC_Clearance\run_create_clearance_page_tool;
use function WC_Clearance\seed_clearance_status_taxonomy;
use const WC_Clearance\CLEARANCE_PAGE_OPTION;
use const WC_Clearance\CLEARANCE_STATUS_CANONICAL_TERM;
use const WC_Clearance\CLEARANCE_STATUS_TAXONOMY;

class Test_Create_Clearance_Page extends WP_UnitTestCase {

	public function test_creates_page_with_title_clearance(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );

		// Act.
		create_clearance_page();

		// Assert.
		$pages = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => 'draft',
				'name'        => 'clearance',
			)
		);
		$this->assertNotEmpty( $pages );
		$this->assertSame( 'Clearance', $pages[0]->post_title );
	}

	public function test_creates_page_with_slug_clearance(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );

		// Act.
		create_clearance_page();

		// Assert.
		$pages = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => 'draft',
				'name'        => 'clearance',
			)
		);
		$this->assertNotEmpty( $pages );
		$this->assertSame( 'clearance', $pages[0]->post_name );
	}

	public function test_creates_page_with_draft_status(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );

		// Act.
		create_clearance_page();

		// Assert.
		$pages = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => 'draft',
				'name'        => 'clearance',
			)
		);
		$this->assertNotEmpty( $pages );
		$this->assertSame( 'draft', $pages[0]->post_status );
	}

	public function test_creates_page_with_clearance_shortcode_on_classic_theme(): void {
		// Arrange.
		switch_theme( 'storefront' );
		delete_option( CLEARANCE_PAGE_OPTION );

		// Act.
		create_clearance_page();

		// Assert.
		$pages = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => 'draft',
				'name'        => 'clearance',
			)
		);
		$this->assertNotEmpty( $pages );
		$this->assertSame( "<!-- wp:shortcode -->\n[products wc_clearance=\"yes\"]\n<!-- /wp:shortcode -->", $pages[0]->post_content );
	}

	public function test_creates_page_with_product_collection_block_on_block_theme(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		seed_clearance_status_taxonomy();
		$canonical_term = get_term_by( 'name', CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );
		$this->assertInstanceOf( WP_Term::class, $canonical_term );
		delete_option( CLEARANCE_PAGE_OPTION );

		// Act.
		create_clearance_page();

		// Assert.
		$pages = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => 'draft',
				'name'        => 'clearance',
			)
		);
		$this->assertNotEmpty( $pages );
		$this->assertStringContainsString( 'wp:woocommerce/product-collection', $pages[0]->post_content );
		$this->assertStringContainsString( (string) $canonical_term->term_id, $pages[0]->post_content );
	}

	public function test_throws_runtime_exception_on_block_theme_when_canonical_term_missing(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		delete_option( CLEARANCE_PAGE_OPTION );

		// Expect.
		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Could not resolve the canonical clearance status term.' );

		// Act.
		create_clearance_page();
	}

	public function test_returns_success_message(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );

		// Act.
		$result = run_create_clearance_page_tool();

		// Assert.
		$this->assertSame( 'Clearance section page created.', $result );
	}

	public function test_saves_page_id_in_option(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );

		// Act.
		create_clearance_page();

		// Assert.
		$pages = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => 'draft',
				'name'        => 'clearance',
			)
		);
		$this->assertNotEmpty( $pages );
		$this->assertSame( $pages[0]->ID, get_option( CLEARANCE_PAGE_OPTION ) );
	}

	public function test_does_not_create_duplicate_page_when_page_already_exists(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );
		create_clearance_page();
		$original_page_id = get_option( CLEARANCE_PAGE_OPTION );

		// Act.
		create_clearance_page();

		// Assert.
		$page_id = get_option( CLEARANCE_PAGE_OPTION );
		$this->assertSame( $original_page_id, $page_id );
	}

	public function test_returns_already_exists_message_when_page_already_exists(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );
		run_create_clearance_page_tool();

		// Act.
		$result = run_create_clearance_page_tool();

		// Assert.
		$this->assertSame( 'Clearance section page already exists.', $result );
	}

	public function test_creates_page_when_existing_page_is_trashed(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );
		$trashed_id = wp_insert_post(
			array(
				'post_title'  => 'Clearance',
				'post_type'   => 'page',
				'post_status' => 'trash',
			)
		);
		update_option( CLEARANCE_PAGE_OPTION, $trashed_id );

		// Act.
		$result = run_create_clearance_page_tool();

		// Assert.
		$this->assertSame( 'Clearance section page created.', $result );
	}

	public function test_returns_could_not_be_created_message_when_option_is_corrupted(): void {
		// Arrange.
		update_option( CLEARANCE_PAGE_OPTION, 'not-an-int' );

		// Act.
		$result = run_create_clearance_page_tool();

		// Assert.
		$this->assertSame( 'Clearance section page could not be created.', $result );
	}

	public function test_throws_runtime_exception_when_option_is_corrupted(): void {
		// Arrange.
		update_option( CLEARANCE_PAGE_OPTION, 'not-an-int' );

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		create_clearance_page();
	}
}
