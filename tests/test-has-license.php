<?php
/**
 * Test the has_license function.
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_license_status;
use function OutletPro\has_license;
use const OutletPro\LICENSE_KEY_OPTION;
use const OutletPro\LICENSE_STATUS_TRANSIENT;

class Test_Has_License extends WP_UnitTestCase {

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

	public function test_returns_true_and_caches_active_license(): void {
		// Arrange.
		$this->mock_license_server_response( true );
		delete_option( LICENSE_KEY_OPTION );
		delete_transient( LICENSE_STATUS_TRANSIENT );
		update_option( LICENSE_KEY_OPTION, 'ab' );

		// Act.
		$result = has_license();

		// Assert.
		$this->assertTrue( $result );
		$this->assertSame( 'active', get_transient( LICENSE_STATUS_TRANSIENT ) );
	}

	public function test_returns_false_and_caches_not_found_license(): void {
		// Arrange.
		$this->mock_license_server_response( false );
		delete_option( LICENSE_KEY_OPTION );
		delete_transient( LICENSE_STATUS_TRANSIENT );
		update_option( LICENSE_KEY_OPTION, 'a' );

		// Act.
		$result = has_license();

		// Assert.
		$this->assertFalse( $result );
		$this->assertSame( 'not_found', get_transient( LICENSE_STATUS_TRANSIENT ) );
	}

	public function test_returns_cached_true_value_without_revalidating(): void {
		// Arrange.
		$this->mock_license_server_response( true );
		delete_option( LICENSE_KEY_OPTION );
		delete_transient( LICENSE_STATUS_TRANSIENT );
		update_option( LICENSE_KEY_OPTION, 'a' );
		set_transient( LICENSE_STATUS_TRANSIENT, 'active', WEEK_IN_SECONDS );

		// Act.
		$result = has_license();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_returns_cached_false_value_without_revalidating(): void {
		// Arrange.
		$this->mock_license_server_response( false );
		delete_option( LICENSE_KEY_OPTION );
		delete_transient( LICENSE_STATUS_TRANSIENT );
		update_option( LICENSE_KEY_OPTION, 'ab' );
		set_transient( LICENSE_STATUS_TRANSIENT, 'not_found', WEEK_IN_SECONDS );

		// Act.
		$result = has_license();

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_rethrows_when_license_validation_fails(): void {
		// Arrange.
		$this->mock_license_server_downtime();
		delete_transient( LICENSE_STATUS_TRANSIENT );
		update_option( LICENSE_KEY_OPTION, 'valid-license' );

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		has_license();
	}

	public function test_rethrows_cached_license_validation_error(): void {
		// Arrange.
		set_transient( LICENSE_STATUS_TRANSIENT, 'error', DAY_IN_SECONDS );

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		has_license();
	}

	public function test_get_license_status_returns_and_caches_validation_error(): void {
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

	public function test_get_license_status_returns_cached_validation_error(): void {
		// Arrange.
		set_transient( LICENSE_STATUS_TRANSIENT, 'error', DAY_IN_SECONDS );

		// Act.
		$result = get_license_status();

		// Assert.
		$this->assertSame( 'error', $result );
	}
}
