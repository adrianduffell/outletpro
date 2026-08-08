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
use function OutletPro\init_admin_menu;
use const OutletPro\HAS_LICENSE_TRANSIENT;
use const OutletPro\LICENSE_KEY_OPTION;

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
				if ( strpos( $url, 'https://api.adrianduffell.store/v1/licenses/validate' ) !== false ) {
					return array(
						'headers'  => array(),
						'body'     => wp_json_encode(
							array(
								'success' => $success,
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
		delete_option( LICENSE_KEY_OPTION );
		delete_transient( HAS_LICENSE_TRANSIENT );
		deinit_admin_menu();

		// Act.
		init_admin_menu();

		// Assert.
		$this->assertIsInt( has_action( 'admin_menu', 'OutletPro\add_welcome_menu_hook' ) );
	}

	public function test_does_not_register_menu_page_when_license_is_active(): void {
		// Arrange.
		$this->mock_license_server_response( true );
		delete_transient( HAS_LICENSE_TRANSIENT );
		update_option( LICENSE_KEY_OPTION, 'valid-license-key' );
		deinit_admin_menu();

		// Act.
		init_admin_menu();

		// Assert.
		$this->assertFalse( has_action( 'admin_menu', 'OutletPro\add_welcome_menu_hook' ) );
	}
}
