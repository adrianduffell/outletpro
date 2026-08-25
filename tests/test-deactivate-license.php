<?php
/**
 * Test the deactivate_license function.
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deactivate_license;

class Test_Deactivate_License extends WP_UnitTestCase {

	/**
	 * Mock the license deactivation response.
	 *
	 * @param bool $deactivated Whether the deactivation succeeds.
	 * @param int  $response_code HTTP response code to return.
	 */
	private function mock_license_server_response( bool $deactivated, int $response_code = 200 ): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) use ( $deactivated, $response_code ) {
				if ( 'https://api.lemonsqueezy.com/v1/licenses/deactivate' !== $url ) {
					return $pre;
				}

				return array(
					'headers'  => array(),
					'body'     => wp_json_encode(
						array(
							'deactivated' => $deactivated,
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
			},
			10,
			3
		);
	}

	public function test_deactivates_license(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		$request_args = null;
		$this->mock_license_server_response( true );
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) use ( &$request_args ) {
				if ( 'https://api.lemonsqueezy.com/v1/licenses/deactivate' !== $url ) {
					return $pre;
				}

				$request_args = $args;

				return $pre;
			},
			5,
			3
		);

		// Act.
		deactivate_license( 'abc123', 'activation-id' );

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

	public function test_throws_when_deactivation_is_rejected(): void {
		// Arrange.
		$this->mock_license_server_response( false );

		// Expect.
		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'License deactivation was rejected.' );

		// Act.
		deactivate_license( 'abc123', 'activation-id' );
	}

	public function test_throws_when_deactivation_response_code_is_not_ok(): void {
		// Arrange.
		$this->mock_license_server_response( false, 400 );

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		deactivate_license( 'abc123', 'activation-id' );
	}

	public function test_throws_when_deactivation_response_is_invalid_json(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		$this->mock_license_server_response( true );
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) {
				if ( 'https://api.lemonsqueezy.com/v1/licenses/deactivate' !== $url ) {
					return $pre;
				}

				return array(
					'headers'  => array(),
					'body'     => 'invalid-json',
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

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		deactivate_license( 'abc123', 'activation-id' );
	}

	public function test_throws_when_activation_id_is_missing(): void {
		// Expect.
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Activation ID cannot be empty.' );

		// Act.
		deactivate_license( 'abc123', '' );
	}

	public function test_throws_when_license_key_is_empty(): void {
		// Expect.
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'License key cannot be empty.' );

		// Act.
		deactivate_license( '', 'activation-id' );
	}
}
