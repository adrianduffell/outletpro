<?php
/**
 * Test the display_clearance_page_state_hook function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\display_clearance_page_state_hook;
use const WC_Clearance\CLEARANCE_PAGE_OPTION;

class Test_Display_Clearance_Page_State extends WP_UnitTestCase {

	public function test_adds_clearance_page_label_when_post_is_clearance_page(): void {
		// Arrange.
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( CLEARANCE_PAGE_OPTION, $page_id );
		$post = get_post( $page_id );

		// Act.
		$result = display_clearance_page_state_hook( array(), $post );

		// Assert.
		$this->assertArrayHasKey( 'wc_clearance_page', $result );
		$this->assertSame( 'Clearance Page', $result['wc_clearance_page'] );
	}

	public function test_does_not_add_label_when_post_is_not_clearance_page(): void {
		// Arrange.
		$clearance_page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$other_page_id     = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( CLEARANCE_PAGE_OPTION, $clearance_page_id );
		$post = get_post( $other_page_id );

		// Act.
		$result = display_clearance_page_state_hook( array(), $post );

		// Assert.
		$this->assertArrayNotHasKey( 'wc_clearance_page', $result );
	}

	public function test_does_not_add_label_when_no_clearance_page_is_set(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$post    = get_post( $page_id );

		// Act.
		$result = display_clearance_page_state_hook( array(), $post );

		// Assert.
		$this->assertArrayNotHasKey( 'wc_clearance_page', $result );
	}

	public function test_preserves_existing_post_states(): void {
		// Arrange.
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( CLEARANCE_PAGE_OPTION, $page_id );
		$post            = get_post( $page_id );
		$existing_states = array( 'existing_key' => 'Existing Label' );

		// Act.
		$result = display_clearance_page_state_hook( $existing_states, $post );

		// Assert.
		$this->assertArrayHasKey( 'existing_key', $result );
		$this->assertArrayHasKey( 'wc_clearance_page', $result );
	}
}
