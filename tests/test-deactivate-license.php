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
use const OutletPro\LICENSE_ACTIVATION_OPTION;
use const OutletPro\LICENSE_STATUS_TRANSIENT;

class Test_Deactivate_License extends WP_UnitTestCase {

	/**
	 * Mock the license deactivation response.
	 *
	 * @param bool $deactivated Whether the deactivation succeeds.
	 * @param bool $valid Whether the license validation succeeds.
	 * @param int  $response_code HTTP response code to return.
	 */
	private function mock_license_server_response( bool $deactivated, bool $valid = true, int $response_code = 200 ): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) use ( $deactivated, $response_code, $valid ) {
				if ( 'https://api.lemonsqueezy.com/v1/licenses/validate' === $url ) {
					return array(
						'headers'  => array(),
						'body'     => wp_json_encode(
							array(
								'valid' => $valid,
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

	public function test_deactivates_license_with_activation_id_and_deletes_stored_activation(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		$request_args = null;
		$this->mock_license_server_response( true );
		update_option( LICENSE_ACTIVATION_OPTION, array( 'abc123', 'stored-activation-id' ) );
		set_transient( LICENSE_STATUS_TRANSIENT, 'active', WEEK_IN_SECONDS );
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) use ( &$request_args ) {
				if ( 'https://api.lemonsqueezy.com/v1/licenses/deactivate' !== $url ) {
					return $pre;
				}

				$request_args = $args;

				return array(
					'headers'  => array(),
					'body'     => wp_json_encode(
						array(
							'deactivated' => true,
							'meta'        => array(
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
		$result = deactivate_license( 'abc123', 'activation-id' );

		// Assert.
		$this->assertTrue( $result );
		$this->assertFalse( get_option( LICENSE_ACTIVATION_OPTION ) );
		$this->assertFalse( get_transient( LICENSE_STATUS_TRANSIENT ) );
		$this->assertIsArray( $request_args );
		$this->assertSame(
			array(
				'license_key' => 'abc123',
				'instance_id' => 'activation-id',
			),
			$request_args['body']
		);
		$this->assertSame( 'application/json', $request_args['headers']['Accept'] );
		$this->assertSame( 'application/x-www-form-urlencoded', $request_args['headers']['Content-Type'] );
	}

	public function test_returns_false_and_keeps_stored_activation_when_deactivation_is_rejected(): void {
		// Arrange.
		$this->mock_license_server_response( false );
		update_option( LICENSE_ACTIVATION_OPTION, array( 'abc123', 'activation-id' ) );

		// Act.
		$result = deactivate_license( 'abc123', 'activation-id' );

		// Assert.
		$this->assertFalse( $result );
		$this->assertSame(
			array( 'abc123', 'activation-id' ),
			get_option( LICENSE_ACTIVATION_OPTION )
		);
	}

	public function test_throws_when_deactivation_response_code_is_not_ok(): void {
		// Arrange.
		$this->mock_license_server_response( false, true, 400 );

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

	public function test_returns_false_without_request_when_activation_id_is_missing(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		$request_was_made = false;
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) use ( &$request_was_made ) {
				if ( 'https://api.lemonsqueezy.com/v1/licenses/deactivate' === $url ) {
					$request_was_made = true;
				}

				return $pre;
			},
			10,
			3
		);

		// Act.
		$result = deactivate_license( 'abc123', '' );

		// Assert.
		$this->assertFalse( $result );
		$this->assertFalse( $request_was_made );
	}

	public function test_returns_false_without_deactivating_when_license_is_invalid(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		$deactivation_request_was_made = false;
		$this->mock_license_server_response( true, false );
		update_option( LICENSE_ACTIVATION_OPTION, array( 'abc123', 'activation-id' ) );
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) use ( &$deactivation_request_was_made ) {
				if ( 'https://api.lemonsqueezy.com/v1/licenses/deactivate' === $url ) {
					$deactivation_request_was_made = true;
				}

				return $pre;
			},
			10,
			3
		);

		// Act.
		$result = deactivate_license( 'abc123', 'activation-id' );

		// Assert.
		$this->assertFalse( $result );
		$this->assertFalse( $deactivation_request_was_made );
		$this->assertSame(
			array( 'abc123', 'activation-id' ),
			get_option( LICENSE_ACTIVATION_OPTION )
		);
	}
}
