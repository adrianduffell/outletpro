<?php
/**
 * Tests for the license activation option hooks.
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_license_settings;
use function OutletPro\init_license_settings;
use const OutletPro\LICENSE_ACTIVATION_ID_OPTION;
use const OutletPro\LICENSE_KEY_OPTION;

class Test_Update_License_Activation_Hook extends WP_UnitTestCase {

	public function test_deactivates_previous_license_and_activates_new_license_when_key_is_updated(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		add_filter(
			'home_url',
			function (): string {
				return 'https://example.com';
			}
		);
		deinit_license_settings();
		delete_option( LICENSE_KEY_OPTION );
		update_option( LICENSE_KEY_OPTION, 'previous-license' );
		update_option( LICENSE_ACTIVATION_ID_OPTION, 'previous-activation-id' );
		$requests = array();
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) use ( &$requests ) {
				$requests[] = array(
					'url'  => $url,
					'body' => $args['body'],
				);

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

				if ( 'https://api.lemonsqueezy.com/v1/licenses/deactivate' === $url ) {
					return array(
						'body'     => wp_json_encode( array( 'deactivated' => true ) ),
						'response' => array( 'code' => 200 ),
					);
				}

				return array(
					'body'     => wp_json_encode(
						array(
							'activated' => true,
							'instance'  => array( 'id' => 'new-activation-id' ),
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
		$this->assertSame(
			array(
				'https://api.lemonsqueezy.com/v1/licenses/validate',
				'https://api.lemonsqueezy.com/v1/licenses/deactivate',
				'https://api.lemonsqueezy.com/v1/licenses/validate',
				'https://api.lemonsqueezy.com/v1/licenses/activate',
			),
			array_column( $requests, 'url' )
		);
		$this->assertSame( 'previous-license', $requests[0]['body']['license_key'] );
		$this->assertSame( 'previous-license', $requests[1]['body']['license_key'] );
		$this->assertSame( 'new-license', $requests[2]['body']['license_key'] );
		$this->assertSame( 'new-license', $requests[3]['body']['license_key'] );
		$this->assertSame( 'new-activation-id', get_option( LICENSE_ACTIVATION_ID_OPTION ) );

		// Cleanup.
		deinit_license_settings();
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
		delete_option( LICENSE_ACTIVATION_ID_OPTION );
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
		$this->assertSame( 'activation-id', get_option( LICENSE_ACTIVATION_ID_OPTION ) );

		// Cleanup.
		deinit_license_settings();
	}

	public function test_deactivates_license_when_key_option_is_deleted(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		deinit_license_settings();
		delete_option( LICENSE_KEY_OPTION );
		update_option( LICENSE_KEY_OPTION, 'previous-license' );
		update_option( LICENSE_ACTIVATION_ID_OPTION, 'activation-id' );
		$deactivation_request_was_made = false;
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) use ( &$deactivation_request_was_made ) {
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

				if ( 'https://api.lemonsqueezy.com/v1/licenses/deactivate' !== $url ) {
					return $pre;
				}

				$deactivation_request_was_made = true;

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
		$this->assertTrue( $deactivation_request_was_made );
		$this->assertFalse( get_option( LICENSE_ACTIVATION_ID_OPTION ) );

		// Cleanup.
		deinit_license_settings();
	}
}
