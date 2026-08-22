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
use const OutletPro\LICENSE_ACTIVATION_OPTION;

class Test_Get_License_Activation extends WP_UnitTestCase {

	public function test_returns_license_activation(): void {
		// Arrange.
		update_option( 'blogname', 'Foo' );
		update_option( LICENSE_ACTIVATION_OPTION, array( 'license-key', 'activation-id', 'Foo' ) );

		// Act.
		$activation = get_license_activation();

		// Assert.
		$this->assertSame( array( 'license-key', 'activation-id', 'Foo' ), $activation );
	}

	public function test_returns_null_when_blog_name_does_not_match(): void {
		// Arrange.
		update_option( 'blogname', 'Foo' );
		update_option( LICENSE_ACTIVATION_OPTION, array( 'license-key', 'activation-id', 'Bar' ) );

		// Act.
		$activation = get_license_activation();

		// Assert.
		$this->assertNull( $activation );
	}

	public function test_returns_null_when_license_activation_is_not_set(): void {
		// Arrange.
		delete_option( LICENSE_ACTIVATION_OPTION );

		// Act.
		$activation = get_license_activation();

		// Assert.
		$this->assertNull( $activation );
	}

	public function test_throws_when_stored_license_activation_is_not_a_tuple(): void {
		// Arrange.
		update_option( LICENSE_ACTIVATION_OPTION, array( 'license-key', 'activation-id' ) );

		// Expect.
		$this->expectException( \UnexpectedValueException::class );

		// Act.
		get_license_activation();
	}

	public function test_throws_when_stored_license_key_is_invalid(): void {
		// Arrange.
		update_option( 'blogname', 'Foo' );
		update_option( LICENSE_ACTIVATION_OPTION, array( '', 'activation-id', 'Foo' ) );

		// Expect.
		$this->expectException( \UnexpectedValueException::class );

		// Act.
		get_license_activation();
	}

	public function test_throws_when_stored_activation_id_is_invalid(): void {
		// Arrange.
		update_option( 'blogname', 'Foo' );
		update_option( LICENSE_ACTIVATION_OPTION, array( 'license-key', 123, 'Foo' ) );

		// Expect.
		$this->expectException( \UnexpectedValueException::class );

		// Act.
		get_license_activation();
	}

	public function test_throws_when_stored_blog_name_is_invalid(): void {
		// Arrange.
		update_option( LICENSE_ACTIVATION_OPTION, array( 'license-key', 'activation-id', 123 ) );

		// Expect.
		$this->expectException( \UnexpectedValueException::class );

		// Act.
		get_license_activation();
	}
}
