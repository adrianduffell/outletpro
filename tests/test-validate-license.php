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
use const OutletPro\LICENSE_ERROR_EXPIRED;
use const OutletPro\LICENSE_ERROR_NOT_FOUND;

class Test_Validate_License extends WP_UnitTestCase {

	/**
	 * Mocks the license server response.
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
	 * @param mixed $valid Whether the license validation succeeds or fails.
	 * @param int $response_code The HTTP response code to simulate.
	 * @param int $product_id The product ID returned by the license server.
	 * @param string $license_status The license status returned by the license server.
	 */
	private function mock_license_server_response( $valid, int $response_code = 200, int $product_id = 1279790, string $license_status = 'active' ): void {  //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) use ( $valid, $response_code, $product_id, $license_status ) {
				if ( strpos( $url, 'https://api.lemonsqueezy.com/v1/licenses/validate' ) !== false ) {
					return array(
						'headers'  => array(),
						'body'     => wp_json_encode(
							array(
								'valid'       => $valid,
								'license_key' => array(
									'status' => $license_status,
								),
								'meta'        => array(
									'product_id' => $product_id,
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


	public function test_returns_true_when_license_is_valid(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		$this->mock_license_server_response( true );

		// Act.
		$result = validate_license( 'abc123' );

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_returns_not_found_wp_error_when_license_is_invalid(): void {
		// Arrange.
		$this->mock_license_server_response( false );

		// Act.
		$result = validate_license( 'invalid-license' );

		// Assert.
		$this->assertWPError( $result );
		$this->assertSame( LICENSE_ERROR_NOT_FOUND, $result->get_error_code() );
	}

	public function test_returns_expired_wp_error_when_license_is_expired(): void {
		// Arrange.
		$this->mock_license_server_response( false, 400, 1279790, 'expired' );

		// Act.
		$result = validate_license( 'expired-license' );

		// Assert.
		$this->assertWPError( $result );
		$this->assertSame( LICENSE_ERROR_EXPIRED, $result->get_error_code() );
	}

	public function test_posts_license_key_to_lemonsqueezy_endpoint(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
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

		// Act.
		validate_license( 'abc123' );

		// Assert.
		$this->assertIsArray( $request_args );
		$this->assertSame( array( 'license_key' => 'abc123' ), $request_args['body'] );
		$this->assertSame( 'application/json', $request_args['headers']['Accept'] );
		$this->assertSame( 'application/x-www-form-urlencoded', $request_args['headers']['Content-Type'] );
	}

	public function test_posts_activation_id_to_lemonsqueezy_endpoint_when_provided(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
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

		// Act.
		validate_license( 'abc123', 'activation-id' );

		// Assert.
		$this->assertIsArray( $request_args );
		$this->assertSame(
			array(
				'license_key' => 'abc123',
				'instance_id' => 'activation-id',
			),
			$request_args['body']
		);
	}

	public function test_throws_when_product_is_not_allowed(): void {
		// Arrange.
		$this->mock_license_server_response( true, 200, 1234567 );

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		validate_license( 'license' );
	}

	public function test_throws_when_response_invalid(): void {
		// Arrange.
		$this->mock_license_server_response( 'error' );

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		validate_license( 'license' );
	}

	public function test_throws_when_remote_request_fails(): void {
		// Arrange.
		$this->mock_license_server_downtime();

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		validate_license( 'invalid-license' );
	}

	public function test_throws_when_remote_response_code_unexpected(): void {
		// Arrange.
		$this->mock_license_server_response( false, 500 );

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		validate_license( 'valid-license' );
	}

	public function test_throws_when_license_key_is_empty(): void {
		// Arrange.
		$license_key = '';

		// Expect.
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'License key must not be empty.' );

		// Act.
		validate_license( $license_key );
	}

	public function test_throws_when_license_key_is_too_short(): void {
		// Arrange.
		$license_key = 'a';

		// Expect.
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'License key is too short.' );

		// Act.
		validate_license( $license_key );
	}

	public function test_throws_when_activation_id_is_empty(): void {
		// Arrange.
		$activation_id = '';

		// Expect.
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Activation ID must not be empty.' );

		// Act.
		validate_license( 'license-key', $activation_id );
	}
}
