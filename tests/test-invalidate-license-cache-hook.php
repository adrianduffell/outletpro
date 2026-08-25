<?php
/**
 * Tests for invalidate_license_cache_hook().
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_license_settings;
use function OutletPro\get_license_status;
use function OutletPro\init_license_settings;
use const OutletPro\LICENSE_ACTIVATION_OPTION;
use const OutletPro\LICENSE_KEY_OPTION;
use const OutletPro\LICENSE_STATUS_TRANSIENT;

class Test_Invalidate_License_Cache_Hook extends WP_UnitTestCase {

	private function mock_license_server_response( bool $success ): void {  //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
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

	public function test_invalidates_transient_when_license_key_is_added(): void {
		// Arrange.
		$this->mock_license_server_response( true );
		deinit_license_settings();
		init_license_settings();
		delete_option( LICENSE_KEY_OPTION );
		$this->assertSame( 'none', get_license_status() );
		update_option( LICENSE_ACTIVATION_OPTION, array( 'new-license-key', 'activation-id' ) );

		// Act.
		update_option( LICENSE_KEY_OPTION, 'new-license-key' );

		// Assert.
		$this->assertSame( 'active', get_license_status() );
	}

	public function test_invalidates_transient_when_license_key_is_updated(): void {
		// Arrange.
		$this->mock_license_server_response( true );
		deinit_license_settings();
		init_license_settings();
		update_option( LICENSE_KEY_OPTION, '0' );
		set_transient( LICENSE_STATUS_TRANSIENT, 'not_found', WEEK_IN_SECONDS );
		$this->assertSame( 'not_found', get_license_status() );
		update_option( LICENSE_ACTIVATION_OPTION, array( 'new-license-key', 'activation-id' ) );

		// Act.
		update_option( LICENSE_KEY_OPTION, 'new-license-key' );

		// Assert.
		$this->assertSame( 'active', get_license_status() );
	}
}
