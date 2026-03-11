<?php
/**
 * Test the create_clearance_page and register_create_clearance_page_tool functions.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\create_clearance_page;
use function WC_Clearance\register_create_clearance_page_tool;

class Test_Create_Clearance_Page extends WP_UnitTestCase {

	public function test_creates_page_with_title_clearance(): void {
		// Arrange.

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

		// Act.
		$result = create_clearance_page();

		// Assert.
		$pages = get_posts(
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

	public function test_register_create_clearance_page_tool_adds_tool(): void {
		// Arrange.

		// Act.
		$tools = register_create_clearance_page_tool( array() );

		// Assert.
		$this->assertArrayHasKey( 'create_clearance_page', $tools );
	}

	public function test_register_create_clearance_page_tool_preserves_existing_tools(): void {
		// Arrange.
		$existing_tools = array( 'existing_tool' => array( 'name' => 'Existing' ) );

		// Act.
		$tools = register_create_clearance_page_tool( $existing_tools );

		// Assert.
		$this->assertArrayHasKey( 'existing_tool', $tools );
		$this->assertArrayHasKey( 'create_clearance_page', $tools );
	}
}
