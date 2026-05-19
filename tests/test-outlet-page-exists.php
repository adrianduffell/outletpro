<?php
/**
 * Test the outlet_page_exists function.
 *
 * @package WC_Outlet
 */

use function WC_Outlet\create_outlet_page;
use function WC_Outlet\outlet_page_exists;
use const WC_Outlet\OUTLET_PAGE_OPTION;

class Test_Outlet_Page_Exists extends WP_UnitTestCase {

	public function test_outlet_page_exists_throws_when_option_is_non_numeric_string(): void {
		// Arrange.
		update_option( OUTLET_PAGE_OPTION, 'not-an-int' );

		// Expect.
		$this->expectException( \UnexpectedValueException::class );

		// Act.
		outlet_page_exists();
	}

	public function test_outlet_page_exists_throws_when_option_is_zero(): void {
		// Arrange.
		update_option( OUTLET_PAGE_OPTION, 0 );

		// Expect.
		$this->expectException( \UnexpectedValueException::class );

		// Act.
		outlet_page_exists();
	}

	public function test_outlet_page_exists_throws_when_option_is_zero_string(): void {
		// Arrange.
		update_option( OUTLET_PAGE_OPTION, '0' );

		// Expect.
		$this->expectException( \UnexpectedValueException::class );

		// Act.
		outlet_page_exists();
	}

	public function test_outlet_page_exists_returns_true_when_option_is_numeric_string(): void {
		// Arrange.
		delete_option( OUTLET_PAGE_OPTION );
		create_outlet_page();
		$page_id = (string) get_option( OUTLET_PAGE_OPTION );
		update_option( OUTLET_PAGE_OPTION, $page_id );

		// Act.
		$result = outlet_page_exists();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_outlet_page_exists_returns_false_when_no_page_exists(): void {
		// Arrange.
		delete_option( OUTLET_PAGE_OPTION );

		// Act.
		$result = outlet_page_exists();

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_outlet_page_exists_returns_true_when_page_exists(): void {
		// Arrange.
		delete_option( OUTLET_PAGE_OPTION );
		create_outlet_page();

		// Act.
		$result = outlet_page_exists();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_outlet_page_exists_returns_false_after_page_is_trashed(): void {
		// Arrange.
		delete_option( OUTLET_PAGE_OPTION );
		create_outlet_page();
		$page_id = get_option( OUTLET_PAGE_OPTION );
		wp_trash_post( $page_id );

		// Act.
		$result = outlet_page_exists();

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_outlet_page_exists_returns_false_after_page_is_deleted(): void {
		// Arrange.
		delete_option( OUTLET_PAGE_OPTION );
		create_outlet_page();
		$page_id = get_option( OUTLET_PAGE_OPTION );
		wp_delete_post( $page_id, true );

		// Act.
		$result = outlet_page_exists();

		// Assert.
		$this->assertFalse( $result );
	}
}
