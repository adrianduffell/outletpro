<?php
/**
 * Test the outlet_page_is_published function.
 *
 * @package WC_Outlet
 */

use function WC_Outlet\outlet_page_is_published;
use function WC_Outlet\create_outlet_page;
use const WC_Outlet\OUTLET_PAGE_OPTION;

class Test_Outlet_Page_Is_Published extends WP_UnitTestCase {

	public function test_returns_false_when_option_does_not_exist(): void {
		// Arrange.
		delete_option( OUTLET_PAGE_OPTION );

		// Act.
		$result = outlet_page_is_published();

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_returns_false_when_page_is_in_draft_status(): void {
		// Arrange.
		delete_option( OUTLET_PAGE_OPTION );
		create_outlet_page();

		// Act.
		$result = outlet_page_is_published();

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_returns_true_when_page_is_published(): void {
		// Arrange.
		delete_option( OUTLET_PAGE_OPTION );
		create_outlet_page();
		$page_id = get_option( OUTLET_PAGE_OPTION );
		wp_publish_post( $page_id );

		// Act.
		$result = outlet_page_is_published();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_returns_false_when_page_is_in_pending_status(): void {
		// Arrange.
		delete_option( OUTLET_PAGE_OPTION );
		create_outlet_page();
		$page_id = get_option( OUTLET_PAGE_OPTION );
		wp_update_post(
			array(
				'ID'          => $page_id,
				'post_status' => 'pending',
			)
		);

		// Act.
		$result = outlet_page_is_published();

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_returns_false_after_page_is_trashed(): void {
		// Arrange.
		delete_option( OUTLET_PAGE_OPTION );
		create_outlet_page();
		$page_id = get_option( OUTLET_PAGE_OPTION );
		wp_publish_post( $page_id );
		wp_trash_post( $page_id );

		// Act.
		$result = outlet_page_is_published();

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_returns_false_after_page_is_deleted(): void {
		// Arrange.
		delete_option( OUTLET_PAGE_OPTION );
		create_outlet_page();
		$page_id = get_option( OUTLET_PAGE_OPTION );
		wp_publish_post( $page_id );
		wp_delete_post( $page_id, true );

		// Act.
		$result = outlet_page_is_published();

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_throws_runtime_exception_when_option_is_non_numeric_string(): void {
		// Arrange.
		update_option( OUTLET_PAGE_OPTION, 'not-an-int' );

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		outlet_page_is_published();
	}

	public function test_throws_runtime_exception_when_option_is_zero(): void {
		// Arrange.
		update_option( OUTLET_PAGE_OPTION, 0 );

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		outlet_page_is_published();
	}

	public function test_throws_runtime_exception_when_option_is_zero_string(): void {
		// Arrange.
		update_option( OUTLET_PAGE_OPTION, '0' );

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		outlet_page_is_published();
	}
}
