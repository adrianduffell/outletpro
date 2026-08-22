<?php
/**
 * Tests for add_welcome_menu_hook().
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_admin_menu;
use function OutletPro\get_license_activation_site_hash;
use function OutletPro\init_admin_menu;
use const OutletPro\DISMISS_COOKIE;
use const OutletPro\LICENSE_ACTIVATION_OPTION_PREFIX;
use const OutletPro\LICENSE_STATUS_TRANSIENT;
use const OutletPro\WELCOME_PAGE_SLUG;

class Test_Add_Welcome_Menu_Hook extends WP_UnitTestCase {

	/**
	 * Mocks the license server response.
	 *
	 * @param bool $success Whether the license validation succeeds or fails.
	 */
	private function mock_license_server_response( bool $success ): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) use ( $success ) {
				if ( strpos( $url, 'https://api.lemonsqueezy.com/v1/licenses/validate' ) !== false ) {
					return array(
						'headers'  => array(),
						'body'     => wp_json_encode(
							array(
								'valid' => $success,
								'meta'  => array(
									'product_id' => 1279790,
								),
							)
						),
						'response' => array(
							'code'    => 200,
							'message' => 'OK',
						),
						'cookies'  => array(),
						'filename' => null,
					);
				}

				return $pre;
			},
			10,
			3
		);
	}

	public function test_registers_menu_page_when_no_license(): void {
		// Arrange.
		unset( $_COOKIE[ DISMISS_COOKIE ] );
		delete_option( LICENSE_ACTIVATION_OPTION_PREFIX . get_license_activation_site_hash() );
		delete_transient( LICENSE_STATUS_TRANSIENT );
		deinit_admin_menu();

		// Act.
		init_admin_menu();

		// Assert.
		$this->assertIsInt( has_action( 'admin_menu', 'OutletPro\add_welcome_menu_hook' ) );
	}

	public function test_does_not_register_menu_page_when_license_is_active(): void {
		// Arrange.
		unset( $_COOKIE[ DISMISS_COOKIE ] );
		$this->mock_license_server_response( true );
		delete_transient( LICENSE_STATUS_TRANSIENT );
		update_option( LICENSE_ACTIVATION_OPTION_PREFIX . get_license_activation_site_hash(), array( 'valid-license-key', 'activation-id' ) );
		deinit_admin_menu();

		// Act.
		init_admin_menu();

		// Assert.
		$this->assertFalse( has_action( 'admin_menu', 'OutletPro\add_welcome_menu_hook' ) );
	}

	public function test_does_not_register_menu_page_when_dismissed_on_device(): void {
		// Arrange.
		$_COOKIE[ DISMISS_COOKIE ] = '1';
		delete_option( LICENSE_ACTIVATION_OPTION_PREFIX . get_license_activation_site_hash() );
		delete_transient( LICENSE_STATUS_TRANSIENT );
		deinit_admin_menu();

		// Act.
		init_admin_menu();

		// Assert.
		$this->assertFalse( has_action( 'admin_menu', 'OutletPro\add_welcome_menu_hook' ) );

		// Cleanup.
		unset( $_COOKIE[ DISMISS_COOKIE ] );
	}

	public function test_registers_menu_page_when_opened_directly_after_dismissal(): void {
		// Arrange.
		$_GET['page']              = WELCOME_PAGE_SLUG;
		$_COOKIE[ DISMISS_COOKIE ] = '1';
		delete_option( LICENSE_ACTIVATION_OPTION_PREFIX . get_license_activation_site_hash() );
		delete_transient( LICENSE_STATUS_TRANSIENT );
		deinit_admin_menu();

		// Act.
		init_admin_menu();

		// Assert.
		$this->assertIsInt( has_action( 'admin_menu', 'OutletPro\add_welcome_menu_hook' ) );

		// Cleanup.
		unset( $_GET['page'] );
		unset( $_COOKIE[ DISMISS_COOKIE ] );
		deinit_admin_menu();
	}

	public function test_registers_menu_page_when_opened_directly_with_active_license(): void {
		// Arrange.
		$_GET['page'] = WELCOME_PAGE_SLUG;
		unset( $_COOKIE[ DISMISS_COOKIE ] );
		set_transient( LICENSE_STATUS_TRANSIENT, 'active' );
		deinit_admin_menu();

		// Act.
		init_admin_menu();

		// Assert.
		$this->assertIsInt( has_action( 'admin_menu', 'OutletPro\add_welcome_menu_hook' ) );

		// Cleanup.
		unset( $_GET['page'] );
		delete_transient( LICENSE_STATUS_TRANSIENT );
		deinit_admin_menu();
	}
}
