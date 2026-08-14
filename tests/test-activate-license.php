<?php
/**
 * Test the activate_license function.
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\activate_license;
use const OutletPro\LICENSE_ACTIVATION_ID_OPTION;
use const OutletPro\LICENSE_STATUS_TRANSIENT;

class Test_Activate_License extends WP_UnitTestCase {

	/**
	 * Mock the license activation response.
	 *
	 * @param bool $activated Whether the activation succeeds.
	 * @param int  $response_code HTTP response code to return.
	 * @param bool $valid Whether the license validation succeeds.
	 */
	private function mock_license_server_response( bool $activated, int $response_code = 200, bool $valid = true ): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		add_filter(
			'home_url',
			function (): string {
				return 'https://example.com';
			},
			10,
			0
		);

		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) use ( $activated, $response_code, $valid ) {
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

				if ( 'https://api.lemonsqueezy.com/v1/licenses/activate' !== $url ) {
					return $pre;
				}

				return array(
					'headers'  => array(),
					'body'     => wp_json_encode(
						array(
							'activated' => $activated,
							'instance'  => array(
								'id' => 'activation-id',
							),
							'meta'      => array(
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

	public function test_activates_license_and_stores_activation_id(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		$request_args = null;
		$this->mock_license_server_response( true );
		delete_option( LICENSE_ACTIVATION_ID_OPTION );
		set_transient( LICENSE_STATUS_TRANSIENT, 'inactive', WEEK_IN_SECONDS );
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) use ( &$request_args ) {
				if ( 'https://api.lemonsqueezy.com/v1/licenses/activate' !== $url ) {
					return $pre;
				}

				$request_args = $args;

				return array(
					'headers'  => array(),
					'body'     => wp_json_encode(
						array(
							'activated' => true,
							'instance'  => array(
								'id' => 'activation-id',
							),
							'meta'      => array(
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
		$result = activate_license( 'abc123' );

		// Assert.
		$this->assertTrue( $result );
		$this->assertSame( 'activation-id', get_option( LICENSE_ACTIVATION_ID_OPTION ) );
		$this->assertFalse( get_transient( LICENSE_STATUS_TRANSIENT ) );
		$this->assertIsArray( $request_args );
		$this->assertSame(
			array(
				'license_key'   => 'abc123',
				'instance_name' => home_url(),
			),
			$request_args['body']
		);
		$this->assertSame( 'application/json', $request_args['headers']['Accept'] );
		$this->assertSame( 'application/x-www-form-urlencoded', $request_args['headers']['Content-Type'] );
	}

	public function test_validates_without_activating_on_a_local_site(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		add_filter(
			'home_url',
			function (): string {
				return 'https://shop.local';
			}
		);
		$request_urls = array();
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) use ( &$request_urls ) {
				$request_urls[] = $url;

				if ( 'https://api.lemonsqueezy.com/v1/licenses/validate' !== $url ) {
					return $pre;
				}

				return array(
					'headers'  => array(),
					'body'     => wp_json_encode(
						array(
							'valid' => true,
							'meta'  => array( 'product_id' => 1279790 ),
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
		$result = activate_license( 'abc123' );

		// Assert.
		$this->assertTrue( $result );
		$this->assertSame(
			array( 'https://api.lemonsqueezy.com/v1/licenses/validate' ),
			$request_urls
		);
	}

	public function test_returns_false_when_activation_is_rejected(): void {
		// Arrange.
		$this->mock_license_server_response( false );
		delete_option( LICENSE_ACTIVATION_ID_OPTION );

		// Act.
		$result = activate_license( 'abc123' );

		// Assert.
		$this->assertFalse( $result );
		$this->assertFalse( get_option( LICENSE_ACTIVATION_ID_OPTION ) );
	}

	public function test_throws_when_activation_response_code_is_not_ok(): void {
		// Arrange.
		$this->mock_license_server_response( false, 400 );

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		activate_license( 'abc123' );
	}

	public function test_throws_when_activation_response_is_invalid_json(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		$this->mock_license_server_response( true );
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) {
				if ( 'https://api.lemonsqueezy.com/v1/licenses/activate' !== $url ) {
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
		activate_license( 'abc123' );
	}

	public function test_throws_when_activation_response_has_no_activation_id(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		$this->mock_license_server_response( true );
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) {
				if ( 'https://api.lemonsqueezy.com/v1/licenses/activate' !== $url ) {
					return $pre;
				}

				return array(
					'headers'  => array(),
					'body'     => wp_json_encode(
						array(
							'activated' => true,
							'meta'      => array(
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

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		activate_license( 'abc123' );
	}

	public function test_returns_false_without_activating_when_license_is_invalid(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		$activation_request_was_made = false;
		$this->mock_license_server_response( true, 200, false );
		delete_option( LICENSE_ACTIVATION_ID_OPTION );
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) use ( &$activation_request_was_made ) {
				if ( 'https://api.lemonsqueezy.com/v1/licenses/activate' === $url ) {
					$activation_request_was_made = true;
				}

				return $pre;
			},
			10,
			3
		);

		// Act.
		$result = activate_license( 'abc123' );

		// Assert.
		$this->assertFalse( $result );
		$this->assertFalse( $activation_request_was_made );
		$this->assertFalse( get_option( LICENSE_ACTIVATION_ID_OPTION ) );
	}
}
