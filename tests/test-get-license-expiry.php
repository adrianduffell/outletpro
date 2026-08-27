<?php
/**
 * Tests for get_license_expiry().
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_license_expiry;
use const OutletPro\LICENSE_ACTIVATION_OPTION;
use const OutletPro\LICENSE_EXPIRY_TRANSIENT;
use const OutletPro\LICENSE_HTTP_CACHE_GROUP;
use const OutletPro\LICENSE_STATUS_TRANSIENT;

class Test_Get_License_Expiry extends WP_UnitTestCase {

	private function mock_validation_response( string $license_key, string $response_body ): string {
		$license_activation = array( $license_key, 'activation-id' );
		$cache_key          = hash( 'sha256', $license_activation[0] . $license_activation[1] );

		set_transient( LICENSE_STATUS_TRANSIENT, 'active' );
		update_option( LICENSE_ACTIVATION_OPTION, $license_activation );
		delete_transient( LICENSE_EXPIRY_TRANSIENT );
		wp_cache_delete( $cache_key, LICENSE_HTTP_CACHE_GROUP );
		mock_http_rest_api_response(
			'POST',
			'https://api.lemonsqueezy.com/v1/licenses/validate',
			$response_body
		);

		return $cache_key;
	}

	public function test_throws_when_site_not_activated(): void {
		// Arrange.
		delete_transient( LICENSE_STATUS_TRANSIENT );
		delete_option( LICENSE_ACTIVATION_OPTION );

		// Expect.
		$this->expectException( RuntimeException::class );

		// Act.
		get_license_expiry();
	}

	public function test_returns_null_for_cached_no_expiry(): void {
		// Arrange.
		set_transient( LICENSE_STATUS_TRANSIENT, 'active' );
		set_transient( LICENSE_EXPIRY_TRANSIENT, array( false ), DAY_IN_SECONDS );

		// Act.
		$result = get_license_expiry();

		// Assert.
		$this->assertNull( $result );
	}

	public function test_returns_cached_expiry(): void {
		// Arrange.
		$expiry = '2050-01-01T00:00:00.000000Z';
		set_transient( LICENSE_STATUS_TRANSIENT, 'active' );
		set_transient( LICENSE_EXPIRY_TRANSIENT, array( true, $expiry ), DAY_IN_SECONDS );

		// Act.
		$result = get_license_expiry();

		// Assert.
		$this->assertInstanceOf( DateTimeImmutable::class, $result );
		$this->assertSame( $expiry, $result->format( 'Y-m-d\TH:i:s.u\Z' ) );
	}

	public function test_uses_cached_http_response_for_expired_license(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		$license_activation = array( 'cached-expired-license', 'activation-id' );
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
		update_option( LICENSE_ACTIVATION_OPTION, $license_activation );
		delete_transient( LICENSE_STATUS_TRANSIENT );
		delete_transient( LICENSE_EXPIRY_TRANSIENT );

		// Act.
		$result = get_license_expiry();

		// Assert.
		$this->assertInstanceOf( DateTimeImmutable::class, $result );
		$this->assertSame( '1997-08-29T06:14:00.000000Z', $result->format( 'Y-m-d\TH:i:s.u\Z' ) );
	}

	public function test_returns_and_caches_expiry_from_validation_response(): void {
		// Arrange.
		$response_body = file_get_contents( dirname( __DIR__ ) . '/fixtures/lemon-squeezy/post-validate-true.json' );
		$cache_key     = $this->mock_validation_response( 'expiring-license', $response_body );
		set_transient( LICENSE_EXPIRY_TRANSIENT, array( true, 'not-a-date' ), DAY_IN_SECONDS );

		// Act.
		$result = get_license_expiry();

		// Assert.
		$expected        = array( true, '2050-01-01T00:00:00.000000Z' );
		$cached_response = wp_cache_get( $cache_key, LICENSE_HTTP_CACHE_GROUP );
		$this->assertInstanceOf( DateTimeImmutable::class, $result );
		$this->assertSame( $expected[1], $result->format( 'Y-m-d\TH:i:s.u\Z' ) );
		$this->assertSame( $expected, get_transient( LICENSE_EXPIRY_TRANSIENT ) );
		$this->assertIsArray( $cached_response );
		$this->assertSame( 200, wp_remote_retrieve_response_code( $cached_response ) );
		$this->assertSame( $response_body, wp_remote_retrieve_body( $cached_response ) );
	}

	public function test_returns_no_expiry_from_lifetime_validation_response(): void {
		// Arrange.
		$response_body = file_get_contents( dirname( __DIR__ ) . '/fixtures/lemon-squeezy/post-validate-true-lifetime.json' );
		$this->mock_validation_response( 'lifetime-license', $response_body );

		// Act.
		$result = get_license_expiry();

		// Assert.
		$this->assertNull( $result );
		$this->assertSame( array( false ), get_transient( LICENSE_EXPIRY_TRANSIENT ) );
	}

	public function test_throws_when_expiry_is_missing(): void {
		// Arrange.
		$response_body = wp_json_encode( array( 'license_key' => array( 'status' => 'active' ) ) );
		$this->mock_validation_response( 'missing-expiry-license', $response_body );

		// Expect.
		$this->expectException( UnexpectedValueException::class );

		// Act.
		get_license_expiry();
	}

	public function test_throws_when_license_has_invalid_type(): void {
		// Arrange.
		$response_body = wp_json_encode( array( 'license_key' => array() ) );
		$this->mock_validation_response( 'invalid-license-type', $response_body );

		// Expect.
		$this->expectException( UnexpectedValueException::class );

		// Act.
		get_license_expiry();
	}

	public function test_throws_when_expiry_is_empty(): void {
		// Arrange.
		$response_body = wp_json_encode( array( 'license_key' => array( 'expires_at' => '' ) ) );
		$this->mock_validation_response( 'empty-expiry-license', $response_body );

		// Expect.
		$this->expectException( UnexpectedValueException::class );

		// Act.
		get_license_expiry();
	}

	public function test_throws_when_expiry_has_invalid_type(): void {
		// Arrange.
		$response_body = wp_json_encode( array( 'license_key' => array( 'expires_at' => array() ) ) );
		$this->mock_validation_response( 'invalid-expiry-license', $response_body );

		// Expect.
		$this->expectException( UnexpectedValueException::class );

		// Act.
		get_license_expiry();
	}

	public function test_throws_when_expiry_is_malformed(): void {
		// Arrange.
		$response_body = wp_json_encode( array( 'license_key' => array( 'expires_at' => 'not-a-date' ) ) );
		$this->mock_validation_response( 'malformed-expiry-license', $response_body );

		// Expect.
		$this->expectException( UnexpectedValueException::class );

		// Act.
		get_license_expiry();
	}
}
