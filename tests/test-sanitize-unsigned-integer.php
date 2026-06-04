<?php
/**
 * Test the sanitize_unsigned_integer function.
 *
 * @package OutletPro
 */

use function OutletPro\sanitize_unsigned_integer;

class Test_Sanitize_Unsigned_Integer extends WP_UnitTestCase {

	public function test_returns_null_when_value_is_null(): void {
		// Arrange.
		$value = null;

		// Act.
		$result = sanitize_unsigned_integer( $value );

		// Assert.
		$this->assertNull( $result );
	}

	public function test_returns_null_for_negative_integer(): void {
		// Arrange.
		$value = -123;

		// Act.
		$result = sanitize_unsigned_integer( $value );

		// Assert.
		$this->assertNull( $result );
	}

	public function test_returns_integer_for_digit_string(): void {
		// Arrange.
		$value = '42';

		// Act.
		$result = sanitize_unsigned_integer( $value );

		// Assert.
		$this->assertSame( 42, $result );
	}

	public function test_returns_null_for_non_digit_string(): void {
		// Arrange.
		$value = '-1';

		// Act.
		$result = sanitize_unsigned_integer( $value );

		// Assert.
		$this->assertNull( $result );
	}

	public function test_returns_integer_for_non_negative_whole_float(): void {
		// Arrange.
		$value = 140.0;

		// Act.
		$result = sanitize_unsigned_integer( $value );

		// Assert.
		$this->assertSame( 140, $result );
	}

	public function test_returns_integer_for_fractional_float(): void {
		// Arrange.
		$value = 140.5;

		// Act.
		$result = sanitize_unsigned_integer( $value );

		// Assert.
		$this->assertSame( 140, $result );
	}

	public function test_returns_null_for_negative_float(): void {
		// Arrange.
		$value = -1.0;

		// Act.
		$result = sanitize_unsigned_integer( $value );

		// Assert.
		$this->assertNull( $result );
	}

	public function test_returns_null_for_non_finite_float(): void {
		// Arrange.
		$value = INF;

		// Act.
		$result = sanitize_unsigned_integer( $value );

		// Assert.
		$this->assertNull( $result );
	}

	public function test_returns_null_for_array(): void {
		// Arrange.
		$value = array();

		// Act.
		$result = sanitize_unsigned_integer( $value );

		// Assert.
		$this->assertNull( $result );
	}

	public function test_returns_null_for_object(): void {
		// Arrange.
		$value = new stdClass();

		// Act.
		$result = sanitize_unsigned_integer( $value );

		// Assert.
		$this->assertNull( $result );
	}
}
