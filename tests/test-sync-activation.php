<?php
/**
 * Test the sync_activation function.
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_license_settings;
use function OutletPro\sync_activation;
use const OutletPro\LICENSE_ACTIVATION_OPTION;
use const OutletPro\LICENSE_KEY_OPTION;

class Test_Sync_Activation extends WP_UnitTestCase {

	public function test_does_nothing_when_license_key_and_activation_are_absent(): void {
		// Arrange.
		deinit_license_settings();
		delete_option( LICENSE_KEY_OPTION );
		delete_option( LICENSE_ACTIVATION_OPTION );

		// Act.
		sync_activation();

		// Assert.
		$this->assertFalse( get_option( LICENSE_ACTIVATION_OPTION ) );
	}

	public function test_does_nothing_when_license_key_matches_stored_activation(): void {
		// Arrange.
		deinit_license_settings();
		update_option( LICENSE_KEY_OPTION, 'license-key' );
		update_option( LICENSE_ACTIVATION_OPTION, array( 'license-key', 'activation-id' ) );

		// Act.
		sync_activation();

		// Assert.
		$this->assertSame(
			array( 'license-key', 'activation-id' ),
			get_option( LICENSE_ACTIVATION_OPTION )
		);
	}

	public function test_activates_license_key_when_it_does_not_match_stored_activation(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		deinit_license_settings();
		update_option( LICENSE_KEY_OPTION, 'new-license' );
		update_option( LICENSE_ACTIVATION_OPTION, array( 'previous-license', 'activation-id' ) );
		$requests = array();
		add_filter(
			'home_url',
			function (): string {
				return 'https://example.com';
			}
		);
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) use ( &$requests ) {
				$requests[] = array(
					'url'  => $url,
					'body' => $args['body'],
				);

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

		// Act.
		sync_activation();

		// Assert.
		$this->assertSame(
			array(
				'https://api.lemonsqueezy.com/v1/licenses/deactivate',
				'https://api.lemonsqueezy.com/v1/licenses/validate',
				'https://api.lemonsqueezy.com/v1/licenses/activate',
			),
			array_column( $requests, 'url' )
		);
		$this->assertSame( 'previous-license', $requests[0]['body']['license_key'] );
		$this->assertSame( 'activation-id', $requests[0]['body']['instance_id'] );
		$this->assertSame(
			array( 'new-license', 'new-activation-id' ),
			get_option( LICENSE_ACTIVATION_OPTION )
		);
	}

	public function test_activates_license_key_when_stored_activation_is_absent(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		deinit_license_settings();
		update_option( LICENSE_KEY_OPTION, 'license-key' );
		delete_option( LICENSE_ACTIVATION_OPTION );
		add_filter(
			'home_url',
			function (): string {
				return 'https://example.com';
			}
		);
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) {
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

		// Act.
		sync_activation();

		// Assert.
		$this->assertSame(
			array( 'license-key', 'activation-id' ),
			get_option( LICENSE_ACTIVATION_OPTION )
		);
	}

	public function test_deletes_stored_activation_when_license_activation_fails(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		deinit_license_settings();
		update_option( LICENSE_KEY_OPTION, 'new-license' );
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

		// Act.
		sync_activation();

		// Assert.
		$this->assertFalse( get_option( LICENSE_ACTIVATION_OPTION ) );
	}

	public function test_deactivates_and_deletes_stored_activation_when_license_key_is_absent(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		deinit_license_settings();
		delete_option( LICENSE_KEY_OPTION );
		update_option( LICENSE_ACTIVATION_OPTION, array( 'license-key', 'activation-id' ) );
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

		// Act.
		sync_activation();

		// Assert.
		$this->assertSame(
			array(
				'license_key' => 'license-key',
				'instance_id' => 'activation-id',
			),
			$request_body
		);
		$this->assertFalse( get_option( LICENSE_ACTIVATION_OPTION ) );
	}
}
