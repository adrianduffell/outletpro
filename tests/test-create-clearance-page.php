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
		$existing_id = (int) get_option( CLEARANCE_PAGE_OPTION );
		if ( $existing_id > 0 ) {
			wp_delete_post( $existing_id, true );
		}
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
		$existing_id = (int) get_option( CLEARANCE_PAGE_OPTION );
		if ( $existing_id > 0 ) {
			wp_delete_post( $existing_id, true );
		}
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
		$existing_id = (int) get_option( CLEARANCE_PAGE_OPTION );
		if ( $existing_id > 0 ) {
			wp_delete_post( $existing_id, true );
		}
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

	public function test_creates_page_with_clearance_shortcode(): void {
		// Arrange.
		$existing_id = (int) get_option( CLEARANCE_PAGE_OPTION );
		if ( $existing_id > 0 ) {
			wp_delete_post( $existing_id, true );
		}
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
		$this->assertSame( '[products is_clearance="yes"]', $pages[0]->post_content );
	}

	public function test_returns_success_message(): void {
		// Arrange.
		$existing_id = (int) get_option( CLEARANCE_PAGE_OPTION );
		if ( $existing_id > 0 ) {
			wp_delete_post( $existing_id, true );
		}
		delete_option( CLEARANCE_PAGE_OPTION );

		// Act.
		$result = run_create_clearance_page_tool();

		// Assert.
		$this->assertSame( 'Clearance section page created.', $result );
	}

	public function test_saves_page_id_in_option(): void {
		// Arrange.
		$existing_id = (int) get_option( CLEARANCE_PAGE_OPTION );
		if ( $existing_id > 0 ) {
			wp_delete_post( $existing_id, true );
		}
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
		$this->assertSame( $pages[0]->ID, (int) get_option( CLEARANCE_PAGE_OPTION ) );
	}

	public function test_does_not_create_duplicate_page_when_page_already_exists(): void {
		// Arrange.
		$existing_id = (int) get_option( CLEARANCE_PAGE_OPTION );
		if ( $existing_id > 0 ) {
			wp_delete_post( $existing_id, true );
		}
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
		$existing_id = (int) get_option( CLEARANCE_PAGE_OPTION );
		if ( $existing_id > 0 ) {
			wp_delete_post( $existing_id, true );
		}
		delete_option( CLEARANCE_PAGE_OPTION );
		run_create_clearance_page_tool();

		// Act.
		$result = run_create_clearance_page_tool();

		// Assert.
		$this->assertSame( 'Clearance section page already exists.', $result );
	}

	public function test_creates_page_when_existing_page_is_trashed(): void {
		// Arrange.
		$existing_id = (int) get_option( CLEARANCE_PAGE_OPTION );
		if ( $existing_id > 0 ) {
			wp_delete_post( $existing_id, true );
		}
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
