<?php
/**
 * Tests for admin_bar_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\init_taxonomies;
use function OutletPro\seed_outlet_status_taxonomy;
use const OutletPro\OUTLET_PAGE_OPTION;

class Test_Admin_Bar_Hook extends WP_UnitTestCase {

	public function test_adds_edit_page_link(): void {
		// Arrange.
		require_once ABSPATH . WPINC . '/class-wp-admin-bar.php';
		init_taxonomies();
		seed_outlet_status_taxonomy();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$this->go_to( get_permalink( $page_id ) );
		$admin_bar = new WP_Admin_Bar();
		$admin_bar->initialize();
		$admin_bar->add_menus();

		// Act.
		do_action_ref_array( 'admin_bar_menu', array( &$admin_bar ) );

		// Assert.
		$edit = $admin_bar->get_node( 'edit' );
		$this->assertNotNull( $edit );
		$this->assertSame( get_post_type_object( 'page' )->labels->edit_item, $edit->title );
		$this->assertSame( get_edit_post_link( $page_id ), $edit->href );
	}

	public function test_requires_page_edit_permission(): void {
		// Arrange.
		require_once ABSPATH . WPINC . '/class-wp-admin-bar.php';
		init_taxonomies();
		seed_outlet_status_taxonomy();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$this->go_to( get_permalink( $page_id ) );
		$admin_bar = new WP_Admin_Bar();
		$admin_bar->initialize();
		$admin_bar->add_menus();

		// Act.
		do_action_ref_array( 'admin_bar_menu', array( &$admin_bar ) );

		// Assert.
		$this->assertNull( $admin_bar->get_node( 'edit' ) );
	}

	public function test_preserves_other_page_edit_links(): void {
		// Arrange.
		require_once ABSPATH . WPINC . '/class-wp-admin-bar.php';
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		update_option( OUTLET_PAGE_OPTION, self::factory()->post->create( array( 'post_type' => 'page' ) ) );
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$this->go_to( get_permalink( $page_id ) );
		$admin_bar = new WP_Admin_Bar();
		$admin_bar->initialize();
		$admin_bar->add_menus();

		// Act.
		do_action_ref_array( 'admin_bar_menu', array( &$admin_bar ) );

		// Assert.
		$this->assertSame( get_edit_post_link( $page_id ), $admin_bar->get_node( 'edit' )->href );
	}
}
