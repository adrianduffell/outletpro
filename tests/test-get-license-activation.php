<?php
/**
 * Test the get_license_activation function.
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_license_activation;
use function OutletPro\get_license_activation_site_hash;
use const OutletPro\LICENSE_ACTIVATION_OPTION_PREFIX;

class Test_Get_License_Activation extends WP_UnitTestCase {

	public function test_returns_license_activation(): void {
		// Arrange.
		update_option( LICENSE_ACTIVATION_OPTION_PREFIX . get_license_activation_site_hash(), array( 'license-key', 'activation-id' ) );

		// Act.
		$activation = get_license_activation();

		// Assert.
		$this->assertSame( array( 'license-key', 'activation-id' ), $activation );
	}

	public function test_returns_null_when_license_activation_is_not_set(): void {
		// Arrange.
		delete_option( LICENSE_ACTIVATION_OPTION_PREFIX . get_license_activation_site_hash() );

		// Act.
		$activation = get_license_activation();

		// Assert.
		$this->assertNull( $activation );
	}

	public function test_does_not_return_another_sites_license_activation(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		$site_url = 'http://first.example/';
		add_filter(
			'home_url',
			function () use ( &$site_url ): string {
				return $site_url;
			}
		);
		update_option( LICENSE_ACTIVATION_OPTION_PREFIX . get_license_activation_site_hash(), array( 'license-key', 'activation-id' ) );
		$site_url = 'http://second.example/';

		// Act.
		$activation = get_license_activation();

		// Assert.
		$this->assertNull( $activation );
	}

	public function test_throws_when_stored_license_activation_is_not_a_tuple(): void {
		// Arrange.
		update_option( LICENSE_ACTIVATION_OPTION_PREFIX . get_license_activation_site_hash(), array( 'license-key' ) );

		// Expect.
		$this->expectException( \UnexpectedValueException::class );

		// Act.
		get_license_activation();
	}

	public function test_throws_when_stored_license_key_is_invalid(): void {
		// Arrange.
		update_option( LICENSE_ACTIVATION_OPTION_PREFIX . get_license_activation_site_hash(), array( '', 'activation-id' ) );

		// Expect.
		$this->expectException( \UnexpectedValueException::class );

		// Act.
		get_license_activation();
	}

	public function test_throws_when_stored_activation_id_is_invalid(): void {
		// Arrange.
		update_option( LICENSE_ACTIVATION_OPTION_PREFIX . get_license_activation_site_hash(), array( 'license-key', 123 ) );

		// Expect.
		$this->expectException( \UnexpectedValueException::class );

		// Act.
		get_license_activation();
	}
}
