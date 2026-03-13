<?php
/**
 * Test the should_enqueue_editor_guide function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\should_enqueue_editor_guide;
use const WC_Clearance\CLEARANCE_PAGE_OPTION;

class Test_Enqueue_Editor_Guide extends WP_UnitTestCase {

	public function test_returns_false_when_clearance_page_option_not_set(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );

		// Act.
		$result = should_enqueue_editor_guide( 1 );

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_returns_false_when_post_id_does_not_match(): void {
		// Arrange.
		update_option( CLEARANCE_PAGE_OPTION, 42 );

		// Act.
		$result = should_enqueue_editor_guide( 99 );

		// Assert.
		$this->assertFalse( $result );
		delete_option( CLEARANCE_PAGE_OPTION );
	}

	public function test_returns_true_when_post_id_matches_clearance_page(): void {
		// Arrange.
		update_option( CLEARANCE_PAGE_OPTION, 42 );

		// Act.
		$result = should_enqueue_editor_guide( 42 );

		// Assert.
		$this->assertTrue( $result );
		delete_option( CLEARANCE_PAGE_OPTION );
	}

	public function test_returns_false_when_clearance_page_option_is_zero(): void {
		// Arrange.
		update_option( CLEARANCE_PAGE_OPTION, 0 );

		// Act.
		$result = should_enqueue_editor_guide( 0 );

		// Assert.
		$this->assertFalse( $result );
		delete_option( CLEARANCE_PAGE_OPTION );
	}
}
