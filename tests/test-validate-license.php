<?php
/**
 * Test the validate_license function.
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\validate_license;

class Test_Validate_License extends WP_UnitTestCase {

	/**
	 * Mocks the license server response.
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
	 * @param mixed $success Whether the license validation succeeds or fails.
	 * @param int $response_code The HTTP response code to simulate.
	 */
	private function mock_license_server_response( $success, int $response_code = 200 ): void {  //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) use ( $success, $response_code ) {
				if ( strpos( $url, 'https://api.adrianduffell.store/v1/licenses/validate' ) !== false ) {
					return array(
						'headers'  => array(),
						'body'     => wp_json_encode(
							array(
								'success' => $success,
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


	public function test_returns_active_when_license_is_valid(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		$this->mock_license_server_response( true );

		// Act.
		$result = validate_license( 'abc123' );

		// Assert.
		$this->assertSame( 'active', $result );
	}

	public function test_returns_not_found_when_license_is_invalid(): void {
		// Arrange.
		$this->mock_license_server_response( false );

		// Act.
		$result = validate_license( 'invalid-license' );

		// Assert.
		$this->assertSame( 'not_found', $result );
	}

	public function test_returns_expired_when_license_is_expired(): void {
		// Arrange.
		$this->mock_license_server_response( false, 200, 'expired' );

		// Act.
		$result = validate_license( 'expired-license' );

		// Assert.
		$this->assertSame( 'expired', $result );
	}

	public function test_returns_error_when_remote_request_fails(): void {
		// Arrange.
		$this->mock_license_server_downtime();

		// Act.
		$result = validate_license( 'invalid-license' );

		// Assert.
		$this->assertSame( 'error', $result );
	}

	public function test_returns_error_when_remote_response_code_unexpected(): void {
		// Arrange.
		$this->mock_license_server_response( false, 500 );

		// Act.
		$result = validate_license( 'valid-license' );

		// Assert.
		$this->assertSame( 'error', $result );
	}

	public function test_returns_error_for_unexpected_not_found_response_code(): void {
		// Arrange.
		$this->mock_license_server_response( false, 404 );

		// Act.
		$result = validate_license( 'missing-license' );

		// Assert.
		$this->assertSame( 'error', $result );
	}

	public function test_returns_not_found_for_empty_string(): void {
		// Arrange.
		$license_key = '';

		// Act.
		$result = validate_license( $license_key );

		// Assert.
		$this->assertSame( 'not_found', $result );
	}

	public function test_returns_not_found_for_null(): void {
		// Arrange.
		$license_key = null;

		// Act.
		$result = validate_license( $license_key );

		// Assert.
		$this->assertSame( 'not_found', $result );
	}

	public function test_returns_not_found_for_non_string_value(): void {
		// Arrange.
		$license_key = 123;

		// Act.
		$result = validate_license( $license_key );

		// Assert.
		$this->assertSame( 'not_found', $result );
	}
}
