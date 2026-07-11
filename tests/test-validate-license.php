<?php
/**
 * Test the validate_license function.
 *
 * @package OutletPro
 */

use function OutletPro\validate_license;

class Test_Validate_License extends WP_UnitTestCase {

	public function test_returns_true_for_string_longer_than_one_character(): void {
		// Arrange.
		$license_key = 'ab';

		// Act.
		$result = validate_license( $license_key );

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_returns_false_for_single_character_string(): void {
		// Arrange.
		$license_key = 'a';

		// Act.
		$result = validate_license( $license_key );

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_returns_false_for_non_string_value(): void {
		// Arrange.
		$license_key = 123;

		// Act.
		$result = validate_license( $license_key );

		// Assert.
		$this->assertFalse( $result );
	}
}
