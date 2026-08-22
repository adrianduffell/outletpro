<?php
/**
 * Test the set_license_activation function.
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\define_license_activation_option;
use function OutletPro\set_license_activation;
use const OutletPro\LICENSE_ACTIVATION_OPTION;

class Test_Set_License_Activation extends WP_UnitTestCase {

	public function test_stores_license_activation(): void {
		// Arrange.
		define_license_activation_option();
		delete_option( LICENSE_ACTIVATION_OPTION );

		// Act.
		set_license_activation( 'license-key', 'activation-id' );

		// Assert.
		$this->assertSame(
			array( 'license-key', 'activation-id' ),
			get_option( LICENSE_ACTIVATION_OPTION )
		);
	}

	public function test_rejects_empty_license_key(): void {
		// Arrange.
		define_license_activation_option();
		delete_option( LICENSE_ACTIVATION_OPTION );

		// Expect.
		$this->expectException( \InvalidArgumentException::class );

		// Act.
		set_license_activation( ' ', 'activation-id' );
	}

	public function test_rejects_short_license_key(): void {
		// Arrange.
		define_license_activation_option();
		delete_option( LICENSE_ACTIVATION_OPTION );

		// Expect.
		$this->expectException( \InvalidArgumentException::class );

		// Act.
		set_license_activation( 'a', 'activation-id' );
	}

	public function test_rejects_empty_activation_id(): void {
		// Arrange.
		define_license_activation_option();
		delete_option( LICENSE_ACTIVATION_OPTION );

		// Expect.
		$this->expectException( \InvalidArgumentException::class );

		// Act.
		set_license_activation( 'license-key', ' ' );
	}
}
