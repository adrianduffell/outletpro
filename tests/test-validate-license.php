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

	private function mock_license_server_response( $success ): void {
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

	private function mock_license_server_downtime(): void {
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

	public function test_returns_true_when_license_is_valid(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		$this->mock_license_server_response( true );

		// Act.
		$result = validate_license( 'abc123' );

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_returns_true_when_license_is_invalid(): void {
		// Arrange.
		$this->mock_license_server_response( false );

		// Act.
		$result = validate_license( 'invalid-license' );

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_throws_when_response_invalid(): void {
		// Arrange.
		$this->mock_license_server_response( 'error' );

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		$result = validate_license( 'license' );
	}

	public function test_throws_when_remote_request_fails(): void {
		// Arrange.
		$this->mock_license_server_downtime();

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		$result = validate_license( 'invalid-license' );
	}

	public function test_returns_false_for_empty_string(): void {
		// Arrange.
		$license_key = '';

		// Act.
		$result = validate_license( $license_key );

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_returns_false_for_null(): void {
		// Arrange.
		$license_key = null;

		// Act.
		$result = validate_license( $license_key );

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_returns_false_for_non_string_value(): void {
		// Arrange.
		$license_key = 123;

		// Act.
		$result = validate_license( $license_key );

		// Assert.
		$this->assertFalse( $result );
	}
}
