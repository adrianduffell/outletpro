<?php
/**
 * Outlet editor screen registration.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\init_outlet_editor;
use const OutletPro\OUTLET_PAGE_OPTION;

class Test_Load_Outlet_Editor_Hook extends WP_UnitTestCase {
	// phpcs:disable Generic.Metrics.NestingLevel.MaxExceeded -- Restore request globals in each test.
	public function test_registers_replacement_after_loading_the_edit_screen(): void {
		// Arrange.
		init_outlet_editor();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$id = $this->factory->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $id );
		$original       = $_GET; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Preserve the simulated request.
		$_GET['post']   = $id;
		$_GET['action'] = 'edit';
		set_current_screen( 'page' );
		$block_editor = get_current_screen()->is_block_editor();
		remove_filter( 'replace_editor', 'OutletPro\replace_outlet_editor_hook', 10 );
		try {
			// Act.
			$early = has_filter( 'replace_editor', 'OutletPro\replace_outlet_editor_hook' );
			do_action( 'load-post.php' ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Core hook name.
			// Assert.
			$this->assertFalse( $early );
			$this->assertSame( 10, has_filter( 'replace_editor', 'OutletPro\replace_outlet_editor_hook' ) );
			$this->assertSame( $block_editor, get_current_screen()->is_block_editor() );
			$this->assertTrue( apply_filters( 'replace_editor', true, get_post( $id ) ) );
			$this->assertFalse( has_action( 'admin_enqueue_scripts', 'OutletPro\enqueue_outlet_editor_hook' ) );
			$this->assertFalse( has_filter( 'admin_body_class', 'OutletPro\outlet_editor_body_class_hook' ) );
		} finally {
			$_GET = $original;
			remove_filter( 'replace_editor', 'OutletPro\replace_outlet_editor_hook', 10 );
		}
	}

	public function test_does_not_replace_the_editor_without_edit_permission(): void {
		// Arrange.
		init_outlet_editor();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'subscriber' ) ) );
		$id = $this->factory->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $id );
		$original       = $_GET; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Preserve the simulated request.
		$_GET['post']   = $id;
		$_GET['action'] = 'edit';
		set_current_screen( 'page' );
		remove_filter( 'replace_editor', 'OutletPro\replace_outlet_editor_hook', 10 );
		try {
			// Act.
			do_action( 'load-post.php' ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Core hook name.
			// Assert.
			$this->assertFalse( has_filter( 'replace_editor', 'OutletPro\replace_outlet_editor_hook' ) );
		} finally {
			$_GET = $original;
		}
	}
}
