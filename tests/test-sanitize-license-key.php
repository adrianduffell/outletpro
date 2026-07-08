<?php
/**
 * Tests for sanitize_license_key().
 *
 * @package OutletPro
 */

use function OutletPro\sanitize_license_key;

class Test_Sanitize_License_Key extends WP_UnitTestCase {

	public function test_returns_valid_16_char_alphanumeric_string_unchanged(): void {
		// Act.
		$result = sanitize_license_key( 'ABCDEF1234567890' );

		// Assert.
		$this->assertSame( 'ABCDEF1234567890', $result );
	}

	public function test_returns_empty_string_for_non_string_value(): void {
		// Act.
		$result = sanitize_license_key( 12345678901234567 );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_returns_empty_string_for_null_value(): void {
		// Act.
		$result = sanitize_license_key( null );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_returns_empty_string_for_empty_input(): void {
		// Act.
		$result = sanitize_license_key( '' );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_returns_empty_string_for_key_shorter_than_16_chars(): void {
		// Act.
		$result = sanitize_license_key( 'ABC123' );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_returns_empty_string_for_key_longer_than_16_chars(): void {
		// Act.
		$result = sanitize_license_key( 'ABCDEF12345678901' );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_returns_empty_string_for_key_with_special_characters(): void {
		// Act.
		$result = sanitize_license_key( 'ABCDEF123456789!' );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_returns_empty_string_for_key_with_spaces(): void {
		// Act.
		$result = sanitize_license_key( 'ABCDEF 234567890' );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_accepts_lowercase_alphanumeric_key(): void {
		// Act.
		$result = sanitize_license_key( 'abcdef1234567890' );

		// Assert.
		$this->assertSame( 'abcdef1234567890', $result );
	}

	public function test_accepts_mixed_case_alphanumeric_key(): void {
		// Act.
		$result = sanitize_license_key( 'aBcDeF1234567890' );

		// Assert.
		$this->assertSame( 'aBcDeF1234567890', $result );
	}

	public function test_accepts_all_numeric_key(): void {
		// Act.
		$result = sanitize_license_key( '1234567890123456' );

		// Assert.
		$this->assertSame( '1234567890123456', $result );
	}

	public function test_accepts_all_uppercase_alpha_key(): void {
		// Act.
		$result = sanitize_license_key( 'ABCDEFGHIJKLMNOP' );

		// Assert.
		$this->assertSame( 'ABCDEFGHIJKLMNOP', $result );
	}
}
