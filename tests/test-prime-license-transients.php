<?php
/**
 * Test the prime_license_transients function.
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\prime_license_transients;
use const OutletPro\LICENSE_ACTIVATION_OPTION;
use const OutletPro\LICENSE_EXPIRY_TRANSIENT;
use const OutletPro\LICENSE_NAME_TRANSIENT;
use const OutletPro\LICENSE_STATUS_TRANSIENT;

class Test_Prime_License_Transients extends WP_UnitTestCase {

	public function test_primes_license_transients(): void {
		// Arrange.
		update_option( LICENSE_ACTIVATION_OPTION, array( 'prime-license-key', 'activation-id' ) );
		delete_transient( LICENSE_STATUS_TRANSIENT );
		delete_transient( LICENSE_NAME_TRANSIENT );
		delete_transient( LICENSE_EXPIRY_TRANSIENT );
		mock_http_rest_api_response(
			'POST',
			'https://api.lemonsqueezy.com/v1/licenses/validate',
			file_get_contents( dirname( __DIR__ ) . '/fixtures/lemon-squeezy/post-validate-true.json' )
		);

		// Act.
		prime_license_transients();

		// Assert.
		$this->assertSame( 'active', get_transient( LICENSE_STATUS_TRANSIENT ) );
		$this->assertSame( 'Long-term service', get_transient( LICENSE_NAME_TRANSIENT ) );
		$this->assertSame(
			array( true, '2050-01-01T00:00:00.000000Z' ),
			get_transient( LICENSE_EXPIRY_TRANSIENT )
		);
	}

	public function test_deletes_license_name_and_expiry_transients_when_license_is_not_found(): void {
		// Arrange.
		set_transient( LICENSE_STATUS_TRANSIENT, 'not_found', WEEK_IN_SECONDS );
		set_transient( LICENSE_NAME_TRANSIENT, 'Long-term service', WEEK_IN_SECONDS );
		set_transient(
			LICENSE_EXPIRY_TRANSIENT,
			array( true, '2050-01-01T00:00:00.000000Z' ),
			DAY_IN_SECONDS
		);

		// Act.
		prime_license_transients();

		// Assert.
		$this->assertSame( 'not_found', get_transient( LICENSE_STATUS_TRANSIENT ) );
		$this->assertFalse( get_transient( LICENSE_NAME_TRANSIENT ) );
		$this->assertFalse( get_transient( LICENSE_EXPIRY_TRANSIENT ) );
	}
}
