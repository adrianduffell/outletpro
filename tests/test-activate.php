<?php
/**
 * Test the activate function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\activate;
use const WC_Clearance\CLEARANCE_PAGE_OPTION;

class Test_Activate extends WP_UnitTestCase {

	public function test_creates_clearance_page_on_activation(): void {
		// Arrange.
		$existing_id = (int) get_option( CLEARANCE_PAGE_OPTION );
		if ( $existing_id > 0 ) {
			wp_delete_post( $existing_id, true );
		}
		delete_option( CLEARANCE_PAGE_OPTION );

		// Act.
		activate();

		// Assert.
		$this->assertGreaterThan( 0, (int) get_option( CLEARANCE_PAGE_OPTION ) );
	}

	public function test_does_not_throw_when_runtime_exception_is_raised(): void {
		// Arrange.
		$callback = static function () {
			return true;
		};
		add_filter( 'wp_insert_post_empty_content', $callback );

		// Act - should not throw.
		activate();

		// Assert.
		remove_filter( 'wp_insert_post_empty_content', $callback );
		$this->assertTrue( true );
	}
}
