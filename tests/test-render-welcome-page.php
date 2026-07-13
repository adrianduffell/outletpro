<?php
/**
 * Tests for render_welcome_page().
 *
 * @package OutletPro
 */

use function OutletPro\init_admin_menu;
use function OutletPro\render_welcome_page;

class Test_Render_Welcome_Page extends WP_UnitTestCase {

	public function test_renders_react_mount_point(): void {
		// Arrange.
		init_admin_menu();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Expect.
		$this->expectOutputRegex( '/id="outletpro-welcome-page-root"/' );

		// Act.
		render_welcome_page();
	}

	public function test_does_not_render_for_non_admin(): void {
		// Arrange.
		init_admin_menu();
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		// Expect.
		$this->expectOutputString( '' );

		// Act.
		render_welcome_page();
	}
}
