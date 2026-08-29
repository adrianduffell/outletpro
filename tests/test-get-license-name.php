<?php
/**
 * Tests for get_license_name().
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_license_name;
use const OutletPro\LICENSE_ACTIVATION_OPTION;
use const OutletPro\LICENSE_HTTP_CACHE_GROUP;
use const OutletPro\LICENSE_NAME_TRANSIENT;
use const OutletPro\LICENSE_STATUS_TRANSIENT;

class Test_Get_License_Name extends WP_UnitTestCase {

	public function test_throws_when_site_not_activated(): void {
		// Arrange.
		delete_transient( LICENSE_STATUS_TRANSIENT );

		// Expect.
		$this->expectException( RuntimeException::class );

		// Act.
		$result = get_license_name();
	}

	public function test_returns_cached_license_name(): void {
		// Arrange.
		set_transient( LICENSE_STATUS_TRANSIENT, 'active' );
		update_option( LICENSE_ACTIVATION_OPTION, array( 'license-key', 'activation-id' ) );
		set_transient( LICENSE_NAME_TRANSIENT, 'Long-term service', WEEK_IN_SECONDS );

		// Act.
		$result = get_license_name();

		// Assert.
		$this->assertSame( 'Long-term service', $result );
	}

	public function test_uses_cached_http_response(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		update_option( LICENSE_ACTIVATION_OPTION, array( 'cached-license', 'activation-id' ) );
		$license_activation = get_option( LICENSE_ACTIVATION_OPTION );
		$cache_key          = hash( 'sha256', $license_activation[0] . $license_activation[1] );
		add_filter(
			'pre_http_request',
			function (): void {
				throw new RuntimeException( 'Unexpected HTTP request.' );
			}
		);
		wp_cache_set(
			$cache_key,
			array(
				'body'     => file_get_contents( dirname( __DIR__ ) . '/fixtures/lemon-squeezy/post-validate-false-expired.json' ),
				'response' => array( 'code' => 400 ),
			),
			LICENSE_HTTP_CACHE_GROUP
		);
		delete_transient( LICENSE_NAME_TRANSIENT );

		// Act.
		$result = get_license_name();

		// Assert.
		$this->assertSame( 'Long-term service', $result );
	}

	public function test_caches_http_response(): void {
		// Arrange.
		$license_activation = array( 'cache-write-license', 'activation-id' );
		$cache_key          = hash( 'sha256', $license_activation[0] . $license_activation[1] );
		$response_body      = file_get_contents( dirname( __DIR__ ) . '/fixtures/lemon-squeezy/post-validate-true.json' );
		mock_http_rest_api_response(
			'POST',
			'https://api.lemonsqueezy.com/v1/licenses/validate',
			array(
				'license_key' => 'cache-write-license',
				'instance_id' => 'activation-id',
			),
			$response_body
		);
		wp_cache_delete( $cache_key, LICENSE_HTTP_CACHE_GROUP );
		update_option( LICENSE_ACTIVATION_OPTION, $license_activation );
		delete_transient( LICENSE_NAME_TRANSIENT );

		// Act.
		get_license_name();

		// Assert.
		$cached_response = wp_cache_get( $cache_key, LICENSE_HTTP_CACHE_GROUP );
		$this->assertIsArray( $cached_response );
		$this->assertSame( 200, wp_remote_retrieve_response_code( $cached_response ) );
		$this->assertSame( $response_body, wp_remote_retrieve_body( $cached_response ) );
	}
}
