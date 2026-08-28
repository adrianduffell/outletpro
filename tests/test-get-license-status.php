<?php
/**
 * Test the get_license_status function.
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_license_status;
use const OutletPro\LICENSE_ACTIVATION_OPTION;
use const OutletPro\LICENSE_HTTP_CACHE_GROUP;
use const OutletPro\LICENSE_STATUS_TRANSIENT;

class Test_Get_License_Status extends WP_UnitTestCase {

	/**
	 * Mocks the license server response.
	 *
	 * @param bool $success Whether the license validation succeeds or fails.
	 * @param string $license_status The license status returned by the license server.
	 * @param int $response_code The HTTP response code to simulate.
	 */
	private function mock_license_server_response( bool $success, string $license_status = 'active', int $response_code = 200 ): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) use ( $success, $license_status, $response_code ) {
				if ( strpos( $url, 'https://api.lemonsqueezy.com/v1/licenses/validate' ) !== false ) {
					return array(
						'headers'  => array(),
						'body'     => wp_json_encode(
							array(
								'valid'       => $success,
								'license_key' => array(
									'status' => $license_status,
								),
								'meta'        => array(
									'product_id' => 1279790,
								),
							)
						),
						'response' => array(
							'code'    => $response_code,
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

	private function mock_license_server_downtime(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) {
				if ( strpos( $url, 'https://api.lemonsqueezy.com/v1/licenses/validate' ) !== false ) {
					return new WP_Error(
						'http_request_failed',
						'Simulated HTTP failure'
					);
				}

				return $pre;
			},
			10,
			3
		);
	}

	public function test_returns_active_and_caches_valid_license(): void {
		// Arrange.
		$this->mock_license_server_response( true );
		delete_transient( LICENSE_STATUS_TRANSIENT );
		update_option( LICENSE_ACTIVATION_OPTION, array( 'ab', 'activation-id' ) );

		// Act.
		$result = get_license_status();

		// Assert.
		$this->assertSame( 'active', $result );
		$this->assertSame( 'active', get_transient( LICENSE_STATUS_TRANSIENT ) );
	}

	public function test_uses_cached_http_response(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		update_option( LICENSE_ACTIVATION_OPTION, array( 'cached-license', 'activation-id' ) );
		$license_activation = get_option( LICENSE_ACTIVATION_OPTION );
		$cache_key          = hash( 'sha256', $license_activation[0] . $license_activation[1] );
		add_filter(
			'pre_http_request',
			function (): void {
				throw new RuntimeException( 'Unexpected HTTP request.' );
			}
		);
		wp_cache_set(
			$cache_key,
			array(
				'body'     => file_get_contents( dirname( __DIR__ ) . '/fixtures/lemon-squeezy/post-validate-false-expired.json' ),
				'response' => array( 'code' => 400 ),
			),
			LICENSE_HTTP_CACHE_GROUP
		);
		delete_transient( LICENSE_STATUS_TRANSIENT );

		// Act.
		$result = get_license_status();

		// Assert.
		$this->assertSame( 'expired', $result );
	}

	public function test_caches_http_response(): void {
		// Arrange.
		$license_activation = array( 'cache-write-license', 'activation-id' );
		$cache_key          = hash( 'sha256', $license_activation[0] . $license_activation[1] );
		$response_body      = file_get_contents( dirname( __DIR__ ) . '/fixtures/lemon-squeezy/post-validate-true.json' );
		mock_http_rest_api_response(
			'POST',
			'https://api.lemonsqueezy.com/v1/licenses/validate',
			array(
				'license_key' => 'cache-write-license',
				'instance_id' => 'activation-id',
			),
			$response_body
		);
		wp_cache_delete( $cache_key, LICENSE_HTTP_CACHE_GROUP );
		update_option( LICENSE_ACTIVATION_OPTION, $license_activation );
		delete_transient( LICENSE_STATUS_TRANSIENT );

		// Act.
		get_license_status();

		// Assert.
		$cached_response = wp_cache_get( $cache_key, LICENSE_HTTP_CACHE_GROUP );
		$this->assertIsArray( $cached_response );
		$this->assertSame( 200, wp_remote_retrieve_response_code( $cached_response ) );
		$this->assertSame( $response_body, wp_remote_retrieve_body( $cached_response ) );
	}

	public function test_validates_stored_license_activation(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		$request_args = null;
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) use ( &$request_args ) {
				if ( 'https://api.lemonsqueezy.com/v1/licenses/validate' !== $url ) {
					return $pre;
				}

				$request_args = $args;

				return array(
					'headers'  => array(),
					'body'     => wp_json_encode(
						array(
							'valid' => true,
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
			},
			10,
			3
		);
		update_option( LICENSE_ACTIVATION_OPTION, array( 'license-key', 'activation-id' ) );
		delete_transient( LICENSE_STATUS_TRANSIENT );

		// Act.
		$result = get_license_status();

		// Assert.
		$this->assertSame( 'active', $result );
		$this->assertIsArray( $request_args );
		$this->assertSame(
			array(
				'license_key' => 'license-key',
				'instance_id' => 'activation-id',
			),
			$request_args['body']
		);
	}

	public function test_returns_not_found_and_caches_invalid_license(): void {
		// Arrange.
		$this->mock_license_server_response( false );
		delete_transient( LICENSE_STATUS_TRANSIENT );
		update_option( LICENSE_ACTIVATION_OPTION, array( 'ab', 'activation-id' ) );

		// Act.
		$result = get_license_status();

		// Assert.
		$this->assertSame( 'not_found', $result );
		$this->assertSame( 'not_found', get_transient( LICENSE_STATUS_TRANSIENT ) );
	}

	public function test_returns_expired_and_caches_expired_license(): void {
		// Arrange.
		$this->mock_license_server_response( false, 'expired', 400 );
		delete_transient( LICENSE_STATUS_TRANSIENT );
		update_option( LICENSE_ACTIVATION_OPTION, array( 'ab', 'activation-id' ) );

		// Act.
		$result = get_license_status();

		// Assert.
		$this->assertSame( 'expired', $result );
		$this->assertSame( 'expired', get_transient( LICENSE_STATUS_TRANSIENT ) );
	}

	public function test_returns_none_and_caches_empty_license(): void {
		// Arrange.
		delete_option( LICENSE_ACTIVATION_OPTION );
		delete_transient( LICENSE_STATUS_TRANSIENT );

		// Act.
		$result = get_license_status();

		// Assert.
		$this->assertSame( 'none', $result );
		$this->assertSame( 'none', get_transient( LICENSE_STATUS_TRANSIENT ) );
	}

	public function test_returns_cached_active_status_without_revalidating(): void {
		// Arrange.
		$this->mock_license_server_response( false );
		delete_transient( LICENSE_STATUS_TRANSIENT );
		set_transient( LICENSE_STATUS_TRANSIENT, 'active', WEEK_IN_SECONDS );

		// Act.
		$result = get_license_status();

		// Assert.
		$this->assertSame( 'active', $result );
	}

	public function test_returns_cached_not_found_status_without_revalidating(): void {
		// Arrange.
		$this->mock_license_server_response( true );
		delete_transient( LICENSE_STATUS_TRANSIENT );
		set_transient( LICENSE_STATUS_TRANSIENT, 'not_found', WEEK_IN_SECONDS );

		// Act.
		$result = get_license_status();

		// Assert.
		$this->assertSame( 'not_found', $result );
	}

	public function test_returns_cached_none_status_without_revalidating(): void {
		// Arrange.
		$this->mock_license_server_response( true );
		set_transient( LICENSE_STATUS_TRANSIENT, 'none', WEEK_IN_SECONDS );

		// Act.
		$result = get_license_status();

		// Assert.
		$this->assertSame( 'none', $result );
	}

	public function test_returns_error_and_caches_validation_failure(): void {
		// Arrange.
		$this->mock_license_server_downtime();
		delete_transient( LICENSE_STATUS_TRANSIENT );
		update_option( LICENSE_ACTIVATION_OPTION, array( 'valid-license', 'activation-id' ) );

		// Act.
		$result = get_license_status();

		// Assert.
		$this->assertSame( 'error', $result );
		$this->assertSame( 'error', get_transient( LICENSE_STATUS_TRANSIENT ) );
	}

	public function test_returns_cached_error_without_revalidating(): void {
		// Arrange.
		$this->mock_license_server_response( true );
		delete_transient( LICENSE_STATUS_TRANSIENT );
		set_transient( LICENSE_STATUS_TRANSIENT, 'error', DAY_IN_SECONDS );

		// Act.
		$result = get_license_status();

		// Assert.
		$this->assertSame( 'error', $result );
	}

	public function test_returns_none_on_malformed_option_value(): void {
		// Arrange.
		update_option( LICENSE_ACTIVATION_OPTION, array( 'malformed' ) );

		// Act.
		$result = get_license_status();

		// Assert.
		$this->assertSame( 'none', $result );
	}
}
