<?php
/**
 * Tests for the pre_update_license_key_hook function.
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_license_settings;
use function OutletPro\init_license_settings;
use const OutletPro\LICENSE_ACTIVATION_OPTION;
use const OutletPro\LICENSE_KEY_OPTION;

class Test_Pre_Update_License_Key_Hook extends WP_UnitTestCase {
	public function test_rest_api_returns_previous_license_when_activation_fails(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		deinit_license_settings();
		delete_option( LICENSE_KEY_OPTION );
		update_option( LICENSE_KEY_OPTION, 'previous-license' );
		update_option( LICENSE_ACTIVATION_OPTION, array( 'previous-license', 'activation-id' ) );
		mock_http_rest_api_response(
			'POST',
			'https://api.lemonsqueezy.com/v1/licenses/validate',
			array( 'license_key' => 'new-license' ),
			file_get_contents( dirname( __DIR__ ) . '/fixtures/lemon-squeezy/post-validate-true.json' )
		);
		mock_http_rest_api_response(
			'POST',
			'https://api.lemonsqueezy.com/v1/licenses/activate',
			array(
				'license_key'   => 'new-license',
				'instance_name' => home_url(),
			),
			file_get_contents( dirname( __DIR__ ) . '/fixtures/lemon-squeezy/post-activate-false.json' )
		);
		init_license_settings();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		$request = new WP_REST_Request( 'POST', '/wp/v2/settings' );
		$request->set_param( LICENSE_KEY_OPTION, 'new-license' );

		// Act.
		$response = rest_do_request( $request );
		$data     = $response->get_data();

		// Assert.
		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( 'previous-license', $data[ LICENSE_KEY_OPTION ] );
		$this->assertSame( 'previous-license', get_option( LICENSE_KEY_OPTION ) );
	}

	public function test_activates_license_when_key_option_is_added(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		add_filter(
			'home_url',
			function (): string {
				return 'https://example.com';
			}
		);
		deinit_license_settings();
		delete_option( LICENSE_KEY_OPTION );
		delete_option( LICENSE_ACTIVATION_OPTION );
		$activation_request_was_made = false;
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) use ( &$activation_request_was_made ) {
				if ( 'https://api.lemonsqueezy.com/v1/licenses/validate' === $url ) {
					return array(
						'body'     => wp_json_encode(
							array(
								'valid' => true,
								'meta'  => array( 'product_id' => 1279790 ),
							)
						),
						'response' => array( 'code' => 200 ),
					);
				}

				if ( 'https://api.lemonsqueezy.com/v1/licenses/activate' !== $url ) {
					return $pre;
				}

				$activation_request_was_made = true;

				return array(
					'body'     => wp_json_encode(
						array(
							'activated' => true,
							'instance'  => array( 'id' => 'activation-id' ),
						)
					),
					'response' => array( 'code' => 200 ),
				);
			},
			10,
			3
		);
		init_license_settings();

		// Act.
		update_option( LICENSE_KEY_OPTION, 'new-license' );

		// Assert.
		$this->assertTrue( $activation_request_was_made );
		$this->assertSame(
			array( 'new-license', 'activation-id' ),
			get_option( LICENSE_ACTIVATION_OPTION )
		);
	}

	public function test_returns_false_when_replacement_license_activation_fails(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		deinit_license_settings();
		delete_option( LICENSE_KEY_OPTION );
		update_option( LICENSE_KEY_OPTION, 'previous-license' );
		update_option( LICENSE_ACTIVATION_OPTION, array( 'previous-license', 'activation-id' ) );
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) {
				if ( 'https://api.lemonsqueezy.com/v1/licenses/deactivate' === $url ) {
					return array(
						'body'     => wp_json_encode( array( 'deactivated' => true ) ),
						'response' => array( 'code' => 200 ),
					);
				}

				if ( 'https://api.lemonsqueezy.com/v1/licenses/validate' === $url ) {
					return array(
						'body'     => wp_json_encode(
							array(
								'valid' => true,
								'meta'  => array( 'product_id' => 1279790 ),
							)
						),
						'response' => array( 'code' => 200 ),
					);
				}

				if ( 'https://api.lemonsqueezy.com/v1/licenses/activate' !== $url ) {
					return $pre;
				}

				return array(
					'body'     => wp_json_encode( array( 'activated' => false ) ),
					'response' => array( 'code' => 200 ),
				);
			},
			10,
			3
		);
		init_license_settings();

		// Act.
		$updated = update_option( LICENSE_KEY_OPTION, 'new-license' );

		// Assert.
		$this->assertFalse( $updated );
		$this->assertSame( 'previous-license', get_option( LICENSE_KEY_OPTION ) );
		$this->assertSame( array( 'previous-license', 'activation-id' ), get_option( LICENSE_ACTIVATION_OPTION ) );
	}

	public function test_deactivates_and_deletes_activation_when_key_option_is_deleted(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		deinit_license_settings();
		delete_option( LICENSE_KEY_OPTION );
		update_option( LICENSE_KEY_OPTION, 'previous-license' );
		update_option( LICENSE_ACTIVATION_OPTION, array( 'previous-license', 'activation-id' ) );
		mock_http_rest_api_response(
			'POST',
			'https://api.lemonsqueezy.com/v1/licenses/validate',
			array(
				'license_key' => 'previous-license',
				'instance_id' => 'activation-id',
			),
			file_get_contents( dirname( __DIR__ ) . '/fixtures/lemon-squeezy/post-validate-true.json' )
		);
		$request_body = null;
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) use ( &$request_body ) {
				if ( 'https://api.lemonsqueezy.com/v1/licenses/deactivate' !== $url ) {
					return $pre;
				}

				$request_body = $args['body'];

				return array(
					'body'     => wp_json_encode( array( 'deactivated' => true ) ),
					'response' => array( 'code' => 200 ),
				);
			},
			10,
			3
		);
		init_license_settings();

		// Act.
		delete_option( LICENSE_KEY_OPTION );

		// Assert.
		$this->assertSame(
			array(
				'license_key' => 'previous-license',
				'instance_id' => 'activation-id',
			),
			$request_body
		);
		$this->assertFalse( get_option( LICENSE_ACTIVATION_OPTION ) );
	}
}
