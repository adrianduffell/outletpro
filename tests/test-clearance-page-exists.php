<?php
/**
 * Test the clearance_page_exists function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\clearance_page_exists;
use function WC_Clearance\create_clearance_page;
use const WC_Clearance\CLEARANCE_PAGE_OPTION;

class Test_Clearance_Page_Exists extends WP_UnitTestCase {

	public function test_clearance_page_exists_throws_when_option_is_not_an_int(): void {
		// Arrange.
		update_option( CLEARANCE_PAGE_OPTION, 'not-an-int' );

		// Expect.
		$this->expectException( \UnexpectedValueException::class );

		// Act.
		clearance_page_exists();
	}

	public function test_clearance_page_exists_returns_false_when_option_is_zero(): void {
		// Arrange.
		update_option( CLEARANCE_PAGE_OPTION, 0 );

		// Expect.
		$this->expectException( \UnexpectedValueException::class );

		// Act.
		$result = clearance_page_exists();
	}

	public function test_clearance_page_exists_returns_false_when_option_is_zero_string(): void {
		// Arrange.
		update_option( CLEARANCE_PAGE_OPTION, '0' );

		// Expect.
		$this->expectException( \UnexpectedValueException::class );

		// Act.
		$result = clearance_page_exists();
	}

	public function test_clearance_page_exists_returns_true_when_option_is_numeric_string(): void {
		// Arrange.
		$existing_id = (int) get_option( CLEARANCE_PAGE_OPTION );
		if ( $existing_id > 0 ) {
			wp_delete_post( $existing_id, true );
		}
		delete_option( CLEARANCE_PAGE_OPTION );
		create_clearance_page();
		$page_id = (string) get_option( CLEARANCE_PAGE_OPTION );
		update_option( CLEARANCE_PAGE_OPTION, $page_id );

		// Act.
		$result = clearance_page_exists();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_clearance_page_exists_returns_false_when_no_page_exists(): void {
		// Arrange.
		$existing_id = (int) get_option( CLEARANCE_PAGE_OPTION );
		if ( $existing_id > 0 ) {
			wp_delete_post( $existing_id, true );
		}
		delete_option( CLEARANCE_PAGE_OPTION );

		// Act.
		$result = clearance_page_exists();

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_clearance_page_exists_returns_true_when_page_exists(): void {
		// Arrange.
		$existing_id = (int) get_option( CLEARANCE_PAGE_OPTION );
		if ( $existing_id > 0 ) {
			wp_delete_post( $existing_id, true );
		}
		delete_option( CLEARANCE_PAGE_OPTION );
		create_clearance_page();

		// Act.
		$result = clearance_page_exists();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_clearance_page_exists_returns_false_after_page_is_deleted(): void {
		// Arrange.
		$existing_id = (int) get_option( CLEARANCE_PAGE_OPTION );
		if ( $existing_id > 0 ) {
			wp_delete_post( $existing_id, true );
		}
		delete_option( CLEARANCE_PAGE_OPTION );
		create_clearance_page();
		$page_id = (int) get_option( CLEARANCE_PAGE_OPTION );
		wp_delete_post( $page_id, true );

		// Act.
		$result = clearance_page_exists();

		// Assert.
		$this->assertFalse( $result );
	}
}
