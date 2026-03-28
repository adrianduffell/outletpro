<?php
/**
 * Test the delete_clearance_page function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\delete_clearance_page;
use const WC_Clearance\CLEARANCE_PAGE_OPTION;

class Test_Delete_Clearance_Page extends WP_UnitTestCase {

	public function test_deletes_post(): void {
		// Arrange.
		$page_id = wp_insert_post(
			array(
				'post_title'  => 'Clearance',
				'post_type'   => 'page',
				'post_status' => 'draft',
			)
		);
		update_option( CLEARANCE_PAGE_OPTION, $page_id );

		// Act.
		delete_clearance_page();

		// Assert.
		$this->assertNull( get_post( $page_id ) );
	}

	public function test_deletes_option(): void {
		// Arrange.
		$page_id = wp_insert_post(
			array(
				'post_title'  => 'Clearance',
				'post_type'   => 'page',
				'post_status' => 'draft',
			)
		);
		update_option( CLEARANCE_PAGE_OPTION, $page_id );

		// Act.
		delete_clearance_page();

		// Assert.
		$this->assertFalse( get_option( CLEARANCE_PAGE_OPTION ) );
	}

	public function test_does_nothing_when_option_does_not_exist(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );

		// Act.
		delete_clearance_page();

		// Assert.
		$this->assertFalse( get_option( CLEARANCE_PAGE_OPTION ) );
	}

	public function test_deletes_option_when_page_does_not_exist(): void {
		// Arrange.
		$page_id = wp_insert_post(
			array(
				'post_title'  => 'Clearance',
				'post_type'   => 'page',
				'post_status' => 'draft',
			)
		);
		update_option( CLEARANCE_PAGE_OPTION, $page_id );
		wp_delete_post( $page_id, true );

		// Act.
		delete_clearance_page();

		// Assert.
		$this->assertFalse( get_option( CLEARANCE_PAGE_OPTION ) );
	}

	public function test_throws_exception_when_option_is_corrupted(): void {
		// Arrange.
		update_option( CLEARANCE_PAGE_OPTION, 'not-an-int' );

		// Expect.
		$this->expectException( \UnexpectedValueException::class );

		// Act.
		delete_clearance_page();
	}
}
