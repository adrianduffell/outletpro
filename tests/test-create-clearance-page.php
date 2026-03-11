<?php
/**
 * Test the create_clearance_page function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\create_clearance_page;
use function WC_Clearance\run_create_clearance_page_tool;
use const WC_Clearance\CLEARANCE_PAGE_OPTION;

class Test_Create_Clearance_Page extends WP_UnitTestCase {

	public function test_creates_page_with_title_clearance(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );
		foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'name' => 'clearance' ) ) as $page ) {
			wp_delete_post( $page->ID, true );
		}

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
		foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'name' => 'clearance' ) ) as $page ) {
			wp_delete_post( $page->ID, true );
		}

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
		foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'name' => 'clearance' ) ) as $page ) {
			wp_delete_post( $page->ID, true );
		}

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

	public function test_creates_page_with_clearance_shortcode(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );
		foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'name' => 'clearance' ) ) as $page ) {
			wp_delete_post( $page->ID, true );
		}

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
		$this->assertSame( '[products is_clearance="yes"]', $pages[0]->post_content );
	}

	public function test_returns_success_message(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );
		foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'name' => 'clearance' ) ) as $page ) {
			wp_delete_post( $page->ID, true );
		}

		// Act.
		$result = run_create_clearance_page_tool();

		// Assert.
		$pages    = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => 'draft',
				'name'        => 'clearance',
			)
		);
		$edit_url = get_edit_post_link( $pages[0]->ID, 'raw' );

		$this->assertStringContainsString( 'Clearance section page created.', $result );
		$this->assertStringContainsString( $edit_url, $result );
	}

	public function test_saves_page_id_in_option(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );
		foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'name' => 'clearance' ) ) as $page ) {
			wp_delete_post( $page->ID, true );
		}

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
		$this->assertSame( $pages[0]->ID, (int) get_option( CLEARANCE_PAGE_OPTION ) );
	}

	public function test_returns_already_exists_message_when_page_already_exists(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );
		foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'name' => 'clearance' ) ) as $page ) {
			wp_delete_post( $page->ID, true );
		}
		run_create_clearance_page_tool();

		// Act.
		$result = run_create_clearance_page_tool();

		// Assert.
		$this->assertStringContainsString( 'Clearance section page already exists.', $result );
	}
}
