<?php
/**
 * Test the clearance_page_in_menu function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\clearance_page_in_menu;
use function WC_Clearance\create_clearance_page;
use const WC_Clearance\CLEARANCE_PAGE_OPTION;

class Test_Clearance_Page_In_Menu extends WP_UnitTestCase {

	public function test_returns_false_when_option_does_not_exist(): void {
		// Arrange.
		$existing_id = get_option( CLEARANCE_PAGE_OPTION );
		if ( $existing_id > 0 ) {
			wp_delete_post( $existing_id, true );
		}
		delete_option( CLEARANCE_PAGE_OPTION );

		// Act.
		$result = clearance_page_in_menu();

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_returns_false_when_page_is_not_in_any_menu(): void {
		// Arrange.
		$existing_id = get_option( CLEARANCE_PAGE_OPTION );
		if ( $existing_id > 0 ) {
			wp_delete_post( $existing_id, true );
		}
		delete_option( CLEARANCE_PAGE_OPTION );
		create_clearance_page();

		// Act.
		$result = clearance_page_in_menu();

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_returns_true_when_page_is_in_a_menu(): void {
		// Arrange.
		$existing_id = get_option( CLEARANCE_PAGE_OPTION );
		if ( $existing_id > 0 ) {
			wp_delete_post( $existing_id, true );
		}
		delete_option( CLEARANCE_PAGE_OPTION );
		create_clearance_page();
		$page_id = get_option( CLEARANCE_PAGE_OPTION );
		$menu_id = wp_create_nav_menu( 'Test Menu' );
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-type'      => 'post_type',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page_id,
				'menu-item-status'    => 'publish',
			)
		);

		// Act.
		$result = clearance_page_in_menu();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_throws_runtime_exception_when_option_is_non_numeric_string(): void {
		// Arrange.
		update_option( CLEARANCE_PAGE_OPTION, 'not-an-int' );

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		clearance_page_in_menu();
	}

	public function test_throws_runtime_exception_when_option_is_zero(): void {
		// Arrange.
		update_option( CLEARANCE_PAGE_OPTION, 0 );

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		clearance_page_in_menu();
	}

	public function test_throws_runtime_exception_when_option_is_zero_string(): void {
		// Arrange.
		update_option( CLEARANCE_PAGE_OPTION, '0' );

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		clearance_page_in_menu();
	}
}
