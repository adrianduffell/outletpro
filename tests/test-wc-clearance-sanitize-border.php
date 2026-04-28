<?php
/**
 * Test the wc_clearance_sanitize_border function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\wc_clearance_sanitize_border;

class Test_Wc_Clearance_Sanitize_Border extends WP_UnitTestCase {

	public function test_returns_empty_array_when_value_is_not_an_array(): void {
		// Arrange.
		$value = '1px solid red';

		// Act.
		$result = wc_clearance_sanitize_border( $value );

		// Assert.
		$this->assertSame( array(), $result );
	}

	public function test_sanitizes_border_control_shape_values(): void {
		// Arrange.
		$value = array(
			'color' => 'red</style><script>alert(1)</script>',
			'style' => 'solid',
			'width' => '2px',
		);

		// Act.
		$result = wc_clearance_sanitize_border( $value );

		// Assert.
		$this->assertSame(
			array(
				'color' => 'red',
				'style' => 'solid',
				'width' => '2px',
			),
			$result
		);
	}

	public function test_returns_expected_keys_with_empty_string_defaults(): void {
		// Arrange.
		$value = array(
			'color' => '#000',
		);

		// Act.
		$result = wc_clearance_sanitize_border( $value );

		// Assert.
		$this->assertSame(
			array(
				'color' => '#000',
				'style' => '',
				'width' => '',
			),
			$result
		);
	}
}
