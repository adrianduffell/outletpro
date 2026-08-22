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
use function OutletPro\get_license_activation_site_hash;
use function OutletPro\init_license_settings;
use const OutletPro\LICENSE_ACTIVATION_OPTION_PREFIX;
use const OutletPro\LICENSE_KEY_OPTION;

class Test_Update_License_Activation_Hook extends WP_UnitTestCase {

	public function test_syncs_activation_when_key_is_updated(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		add_filter(
			'home_url',
			function (): string {
				return 'https://example.com';
			},
			10,
			0
		);
		deinit_license_settings();
		delete_option( LICENSE_KEY_OPTION );
		update_option( LICENSE_KEY_OPTION, 'previous-license' );
		update_option( LICENSE_ACTIVATION_OPTION_PREFIX . get_license_activation_site_hash(), array( 'previous-license', 'previous-activation-id' ) );
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
				'https://api.lemonsqueezy.com/v1/licenses/activate',
			),
			array_column( $requests, 'url' )
		);
		$this->assertSame( 'new-license', $requests[0]['body']['license_key'] );
		$this->assertSame( 'new-license', $requests[1]['body']['license_key'] );
		$this->assertSame(
			array( 'new-license', 'new-activation-id' ),
			get_option( LICENSE_ACTIVATION_OPTION_PREFIX . get_license_activation_site_hash() )
		);

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
		delete_option( LICENSE_ACTIVATION_OPTION_PREFIX . get_license_activation_site_hash() );
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
			get_option( LICENSE_ACTIVATION_OPTION_PREFIX . get_license_activation_site_hash() )
		);

		// Cleanup.
		deinit_license_settings();
	}

	public function test_deletes_activation_when_key_option_is_deleted(): void {
		// Arrange.
		deinit_license_settings();
		delete_option( LICENSE_KEY_OPTION );
		update_option( LICENSE_KEY_OPTION, 'previous-license' );
		update_option( LICENSE_ACTIVATION_OPTION_PREFIX . get_license_activation_site_hash(), array( 'previous-license', 'activation-id' ) );
		init_license_settings();

		// Act.
		delete_option( LICENSE_KEY_OPTION );

		// Assert.
		$this->assertFalse( get_option( LICENSE_ACTIVATION_OPTION_PREFIX . get_license_activation_site_hash() ) );

		// Cleanup.
		deinit_license_settings();
	}
}
