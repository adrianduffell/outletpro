<?php
/**
 * Test the create_clearance_page function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\create_clearance_page;

class Test_Create_Clearance_Page extends WP_UnitTestCase {

	public function test_creates_page_with_title_clearance(): void {
		// Arrange.
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
		foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'name' => 'clearance' ) ) as $page ) {
			wp_delete_post( $page->ID, true );
		}

		// Act.
		$result = create_clearance_page();

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
}
