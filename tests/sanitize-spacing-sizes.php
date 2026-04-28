<?php
/**
 * Test the sanitize_spacing_sizes function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\sanitize_spacing_sizes;

class Test_Sanitize_Spacing_Sizes extends WP_UnitTestCase {

	public function test_returns_empty_array_when_value_is_not_an_array(): void {
		// Arrange.
		$value = '12px';

		// Act.
		$result = sanitize_spacing_sizes( $value );

		// Assert.
		$this->assertSame( array(), $result );
	}

	public function test_sanitizes_spacing_sizes_control_shape_values(): void {
		// Arrange.
		$value = array(
			'top'    => '10px',
			'right'  => 'calc(1rem + 2px)',
			'bottom' => '2em; color: red;',
			'left'   => 'var(--wp--preset--spacing--20)</style><script>alert(1)</script>',
		);

		// Act.
		$result = sanitize_spacing_sizes( $value );

		// Assert.
		$this->assertSame(
			array(
				'top'    => '10px',
				'right'  => 'calc(1rem + 2px)',
				'bottom' => '',
				'left'   => 'var(--wp--preset--spacing--20)',
			),
			$result
		);
	}

	public function test_returns_expected_keys_with_empty_string_defaults(): void {
		// Arrange.
		$value = array(
			'top' => '1rem',
		);

		// Act.
		$result = sanitize_spacing_sizes( $value );

		// Assert.
		$this->assertSame(
			array(
				'top'    => '1rem',
				'right'  => '',
				'bottom' => '',
				'left'   => '',
			),
			$result
		);
	}

	public function test_returns_empty_strings_for_non_scalar_fields(): void {
		// Arrange.
		$value = array(
			'top'    => array( '1rem' ),
			'right'  => (object) array( 'value' => '2rem' ),
			'bottom' => array(),
			'left'   => null,
		);

		// Act.
		$result = sanitize_spacing_sizes( $value );

		// Assert.
		$this->assertSame(
			array(
				'top'    => '',
				'right'  => '',
				'bottom' => '',
				'left'   => '',
			),
			$result
		);
	}
}
