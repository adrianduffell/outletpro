<?php
/**
 * Test the outlet_page_label_hook function.
 *
 * @package OutletPro
 */

use function OutletPro\init_admin_page_list_table;
use const OutletPro\OUTLET_PAGE_OPTION;

class Test_Outlet_Page_Label_Hook extends WP_UnitTestCase {

	public function test_adds_outlet_page_label_when_post_is_outlet_page(): void {
		// Arrange.
		init_admin_page_list_table();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$post = get_post( $page_id );

		// Act.
		$result = apply_filters( 'display_post_states', array(), $post );

		// Assert.
		$this->assertArrayHasKey( 'outletpro_page', $result );
		$this->assertSame( 'Outlet Page', $result['outletpro_page'] );
	}

	public function test_does_not_add_label_when_post_is_not_outlet_page(): void {
		// Arrange.
		init_admin_page_list_table();
		$outlet_page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$other_page_id  = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $outlet_page_id );
		$post = get_post( $other_page_id );

		// Act.
		$result = apply_filters( 'display_post_states', array(), $post );

		// Assert.
		$this->assertArrayNotHasKey( 'outletpro_page', $result );
	}

	public function test_adds_outlet_page_label_when_option_is_string(): void {
		// Arrange.
		init_admin_page_list_table();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, (string) $page_id );
		$post = get_post( $page_id );

		// Act.
		$result = apply_filters( 'display_post_states', array(), $post );

		// Assert.
		$this->assertArrayHasKey( 'outletpro_page', $result );
		$this->assertSame( 'Outlet Page', $result['outletpro_page'] );
	}

	public function test_does_not_add_label_when_option_is_corrupted(): void {
		// Arrange.
		init_admin_page_list_table();
		update_option( OUTLET_PAGE_OPTION, 'not-a-number' );
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$post    = get_post( $page_id );

		// Act.
		$result = apply_filters( 'display_post_states', array(), $post );

		// Assert.
		$this->assertArrayNotHasKey( 'outletpro_page', $result );
	}

	public function test_does_not_add_label_when_no_outlet_page_is_set(): void {
		// Arrange.
		init_admin_page_list_table();
		delete_option( OUTLET_PAGE_OPTION );
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$post    = get_post( $page_id );

		// Act.
		$result = apply_filters( 'display_post_states', array(), $post );

		// Assert.
		$this->assertArrayNotHasKey( 'outletpro_page', $result );
	}

	public function test_preserves_existing_post_states(): void {
		// Arrange.
		init_admin_page_list_table();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$post            = get_post( $page_id );
		$existing_states = array( 'existing_key' => 'Existing Label' );

		// Act.
		$result = apply_filters( 'display_post_states', $existing_states, $post );

		// Assert.
		$this->assertArrayHasKey( 'existing_key', $result );
		$this->assertArrayHasKey( 'outletpro_page', $result );
	}
}
