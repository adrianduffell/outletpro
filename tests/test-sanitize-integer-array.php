<?php
/**
 * Tests for sanitize_integer_array().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use PHPUnit\Framework\TestCase;
use function OutletPro\sanitize_integer_array;

class Test_Sanitize_Integer_Array extends TestCase {

	// phpcs:ignore Generic.Commenting.DocComment.MissingShort -- PHPUnit data provider metadata.
	/**
	 * @dataProvider integer_arrays
	 * @param mixed $values Input values.
	 * @param int[] $expected Sanitized values.
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
	 */
	public function test_sanitizes_integer_array_without_changing_input( $values, array $expected ): void {
		// Arrange.
		$original = $values;

		// Act.
		$result = sanitize_integer_array( $values );

		// Assert.
		$this->assertSame( $expected, $result );
		$this->assertSame( $original, $values );
	}

	/**
	 * Inputs and explicit expected results for integer array sanitization.
	 *
	 * @return array<string, array{0: mixed, 1: int[]}> Sanitization cases.
	 */
	public static function integer_arrays(): array {
		return array(
			'empty array'         => array( array(), array() ),
			'unchanged integers'  => array( array( 30, 50, 70 ), array( 30, 50, 70 ) ),
			'input order'         => array( array( 70, 30, 50 ), array( 70, 30, 50 ) ),
			'duplicates'          => array( array( 30, 50, 30, 70, 50 ), array( 30, 50, 70 ) ),
			'sparse indexes'      => array(
				array(
					2 => 30,
					7 => 70,
				),
				array( 30, 70 ),
			),
			'associative indexes' => array(
				array(
					'first' => 30,
					'last'  => 70,
				),
				array( 30, 70 ),
			),
			'non-integer entries' => array( array( 30, '50', 50.0, 50.5, true, null, array(), 70 ), array( 30, 70 ) ),
			'negative and zero'   => array( array( -50, 0, 50 ), array( -50, 0, 50 ) ),
			'large integers'      => array( array( PHP_INT_MAX - 1, PHP_INT_MAX ), array( PHP_INT_MAX - 1, PHP_INT_MAX ) ),
			'unrestricted values' => array( array( -1, 0, 50, 100, 101, 500000 ), array( -1, 0, 50, 100, 101, 500000 ) ),
			'all entries removed' => array( array( '30', false, 30.5 ), array() ),
			'null'                => array( null, array() ),
			'string'              => array( '30,50,70', array() ),
			'integer'             => array( 30, array() ),
			'boolean'             => array( false, array() ),
			'object'              => array( new stdClass(), array() ),
		);
	}
}
