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
use const OutletPro\LICENSE_KEY_OPTION;
use const OutletPro\LICENSE_STATUS_TRANSIENT;

class Test_Get_License_Status extends WP_UnitTestCase {

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

	private function mock_license_server_downtime(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) {
				if ( strpos( $url, 'https://api.adrianduffell.store/v1/licenses/validate' ) !== false ) {
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
		delete_option( LICENSE_KEY_OPTION );
		delete_transient( LICENSE_STATUS_TRANSIENT );
		update_option( LICENSE_KEY_OPTION, 'ab' );

		// Act.
		$result = get_license_status();

		// Assert.
		$this->assertSame( 'active', $result );
		$this->assertSame( 'active', get_transient( LICENSE_STATUS_TRANSIENT ) );
	}

	public function test_returns_not_found_and_caches_invalid_license(): void {
		// Arrange.
		$this->mock_license_server_response( false );
		delete_option( LICENSE_KEY_OPTION );
		delete_transient( LICENSE_STATUS_TRANSIENT );
		update_option( LICENSE_KEY_OPTION, 'ab' );

		// Act.
		$result = get_license_status();

		// Assert.
		$this->assertSame( 'not_found', $result );
		$this->assertSame( 'not_found', get_transient( LICENSE_STATUS_TRANSIENT ) );
	}

	public function test_returns_cached_active_status_without_revalidating(): void {
		// Arrange.
		$this->mock_license_server_response( false );
		delete_option( LICENSE_KEY_OPTION );
		delete_transient( LICENSE_STATUS_TRANSIENT );
		update_option( LICENSE_KEY_OPTION, 'ab' );
		set_transient( LICENSE_STATUS_TRANSIENT, 'active', WEEK_IN_SECONDS );

		// Act.
		$result = get_license_status();

		// Assert.
		$this->assertSame( 'active', $result );
	}

	public function test_returns_cached_not_found_status_without_revalidating(): void {
		// Arrange.
		$this->mock_license_server_response( true );
		delete_option( LICENSE_KEY_OPTION );
		delete_transient( LICENSE_STATUS_TRANSIENT );
		update_option( LICENSE_KEY_OPTION, 'ab' );
		set_transient( LICENSE_STATUS_TRANSIENT, 'not_found', WEEK_IN_SECONDS );

		// Act.
		$result = get_license_status();

		// Assert.
		$this->assertSame( 'not_found', $result );
	}

	public function test_returns_error_and_caches_validation_failure(): void {
		// Arrange.
		$this->mock_license_server_downtime();
		delete_transient( LICENSE_STATUS_TRANSIENT );
		update_option( LICENSE_KEY_OPTION, 'valid-license' );

		// Act.
		$result = get_license_status();

		// Assert.
		$this->assertSame( 'error', $result );
		$this->assertSame( 'error', get_transient( LICENSE_STATUS_TRANSIENT ) );
	}

	public function test_returns_cached_error_without_revalidating(): void {
		// Arrange.
		$this->mock_license_server_response( true );
		delete_option( LICENSE_KEY_OPTION );
		delete_transient( LICENSE_STATUS_TRANSIENT );
		update_option( LICENSE_KEY_OPTION, 'valid-license' );
		set_transient( LICENSE_STATUS_TRANSIENT, 'error', DAY_IN_SECONDS );

		// Act.
		$result = get_license_status();

		// Assert.
		$this->assertSame( 'error', $result );
	}
}
