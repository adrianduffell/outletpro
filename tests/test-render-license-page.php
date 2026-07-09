<?php
/**
 * Tests for render_license_page().
 *
 * @package OutletPro
 */

use function OutletPro\register_license_key_setting;
use function OutletPro\render_license_page;
use const OutletPro\LICENSE_KEY_OPTION;

class Test_Render_License_Page extends WP_UnitTestCase {

	public function test_renders_license_key_field(): void {
		// Arrange.
		register_license_key_setting();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Expect.
		$this->expectOutputRegex( '/License Key/' );

		// Act.
		render_license_page();
	}

	public function test_renders_license_key_input_with_saved_value(): void {
		// Arrange.
		register_license_key_setting();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		update_option( LICENSE_KEY_OPTION, 'ABCDEF1234567890' );

		// Expect.
		$this->expectOutputRegex( '/ABCDEF1234567890/' );

		// Act.
		render_license_page();

		// Cleanup.
		delete_option( LICENSE_KEY_OPTION );
	}

	public function test_does_not_render_for_non_admin_user(): void {
		// Arrange.
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		// Expect.
		$this->expectOutputString( '' );

		// Act.
		render_license_page();
	}

	public function test_renders_form_with_options_php_action(): void {
		// Arrange.
		register_license_key_setting();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Expect.
		$this->expectOutputRegex( '/options\.php/' );

		// Act.
		render_license_page();
	}

	public function test_renders_license_key_input_field_name(): void {
		// Arrange.
		register_license_key_setting();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Expect.
		$this->expectOutputRegex( '/name="' . LICENSE_KEY_OPTION . '"/' );

		// Act.
		render_license_page();
	}
}
