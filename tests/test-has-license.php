<?php
/**
 * Test the has_license function.
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\has_license;
use const OutletPro\HAS_LICENSE_TRANSIENT;
use const OutletPro\LICENSE_KEY_OPTION;

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

	public function test_returns_true_and_caches_valid_license(): void {
		// Arrange.
		$this->mock_license_server_response( true );
		delete_option( LICENSE_KEY_OPTION );
		delete_transient( HAS_LICENSE_TRANSIENT );
		update_option( LICENSE_KEY_OPTION, 'ab' );

		// Act.
		$result = has_license();

		// Assert.
		$this->assertTrue( $result );
		$this->assertSame( 'yes', get_transient( HAS_LICENSE_TRANSIENT ) );
	}

	public function test_returns_false_and_caches_invalid_license(): void {
		// Arrange.
		$this->mock_license_server_response( false );
		delete_option( LICENSE_KEY_OPTION );
		delete_transient( HAS_LICENSE_TRANSIENT );
		update_option( LICENSE_KEY_OPTION, 'a' );

		// Act.
		$result = has_license();

		// Assert.
		$this->assertFalse( $result );
		$this->assertSame( 'no', get_transient( HAS_LICENSE_TRANSIENT ) );
	}

	public function test_returns_cached_true_value_without_revalidating(): void {
		// Arrange.
		$this->mock_license_server_response( true );
		delete_option( LICENSE_KEY_OPTION );
		delete_transient( HAS_LICENSE_TRANSIENT );
		update_option( LICENSE_KEY_OPTION, 'a' );
		set_transient( HAS_LICENSE_TRANSIENT, 'yes', WEEK_IN_SECONDS );

		// Act.
		$result = has_license();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_returns_cached_false_value_without_revalidating(): void {
		// Arrange.
		$this->mock_license_server_response( false );
		delete_option( LICENSE_KEY_OPTION );
		delete_transient( HAS_LICENSE_TRANSIENT );
		update_option( LICENSE_KEY_OPTION, 'ab' );
		set_transient( HAS_LICENSE_TRANSIENT, 'no', WEEK_IN_SECONDS );

		// Act.
		$result = has_license();

		// Assert.
		$this->assertFalse( $result );
	}

	public function rethrows_when_license_validation_fails(): void {
		// Arrange.
		$this->mock_license_server_downtime();
		delete_transient( HAS_LICENSE_TRANSIENT );
		update_option( LICENSE_KEY_OPTION, 'valid-license' );

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		has_license();
	}
}
