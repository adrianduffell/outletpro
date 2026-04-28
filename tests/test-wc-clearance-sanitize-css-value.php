<?php
/**
 * Test the wc_clearance_sanitize_css_value function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\wc_clearance_sanitize_css_value;

class Test_Wc_Clearance_Sanitize_Css_Value extends WP_UnitTestCase {

	public function test_returns_valid_hex_color(): void {
		// Arrange.
		$value = '#FF0000';

		// Act.
		$result = wc_clearance_sanitize_css_value( $value );

		// Assert.
		$this->assertSame( '#FF0000', $result );
	}

	public function test_returns_empty_string_when_value_contains_semicolon(): void {
		// Arrange.
		$value = '#FF0000; color: red';

		// Act.
		$result = wc_clearance_sanitize_css_value( $value );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_returns_empty_string_when_value_contains_open_brace(): void {
		// Arrange.
		$value = 'red { color: blue';

		// Act.
		$result = wc_clearance_sanitize_css_value( $value );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_returns_empty_string_when_value_contains_close_brace(): void {
		// Arrange.
		$value = 'red } color: blue';

		// Act.
		$result = wc_clearance_sanitize_css_value( $value );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_removes_scripts(): void {
		// Arrange.
		$value = '<script>alert(1)</script>#FF0000';

		// Act.
		$result = wc_clearance_sanitize_css_value( $value );

		// Assert.
		$this->assertSame( '#FF0000', $result );
	}

	public function test_removes_style_end_tag_injection(): void {
		// Arrange.
		$value = 'red</style><script>alert(1)</script>';

		// Act.
		$result = wc_clearance_sanitize_css_value( $value );

		// Assert.
		$this->assertSame( 'red', $result );
	}

	public function test_returns_valid_rgba_value(): void {
		// Arrange.
		$value = 'rgba(255, 0, 0, 0.5)';

		// Act.
		$result = wc_clearance_sanitize_css_value( $value );

		// Assert.
		$this->assertSame( 'rgba(255, 0, 0, 0.5)', $result );
	}

	public function test_returns_valid_calc_value(): void {
		// Arrange.
		$value = 'calc(100% - 2px)';

		// Act.
		$result = wc_clearance_sanitize_css_value( $value );

		// Assert.
		$this->assertSame( 'calc(100% - 2px)', $result );
	}

	public function test_returns_valid_nested_calc_value(): void {
		// Arrange.
		$value = 'calc(1rem + calc(2px * 3))';

		// Act.
		$result = wc_clearance_sanitize_css_value( $value );

		// Assert.
		$this->assertSame( 'calc(1rem + calc(2px * 3))', $result );
	}

	public function test_returns_valid_var_value(): void {
		// Arrange.
		$value = 'var(--my-spacing, 10px)';

		// Act.
		$result = wc_clearance_sanitize_css_value( $value );

		// Assert.
		$this->assertSame( 'var(--my-spacing, 10px)', $result );
	}

	public function test_returns_valid_nested_var_value(): void {
		// Arrange.
		$value = 'var(--a, var(--b, 5px))';

		// Act.
		$result = wc_clearance_sanitize_css_value( $value );

		// Assert.
		$this->assertSame( 'var(--a, var(--b, 5px))', $result );
	}

	public function test_returns_valid_clamp_value(): void {
		// Arrange.
		$value = 'clamp(0.75rem, 1vw, 1rem)';

		// Act.
		$result = wc_clearance_sanitize_css_value( $value );

		// Assert.
		$this->assertSame( 'clamp(0.75rem, 1vw, 1rem)', $result );
	}

	public function test_returns_valid_color_mix_value(): void {
		// Arrange.
		$value = 'color-mix(in srgb, red 40%, blue)';

		// Act.
		$result = wc_clearance_sanitize_css_value( $value );

		// Assert.
		$this->assertSame( 'color-mix(in srgb, red 40%, blue)', $result );
	}

	public function test_returns_valid_oklch_value(): void {
		// Arrange.
		$value = 'oklch(70% 0.15 200)';

		// Act.
		$result = wc_clearance_sanitize_css_value( $value );

		// Assert.
		$this->assertSame( 'oklch(70% 0.15 200)', $result );
	}

	public function test_returns_valid_unitless_decimal_rem_value(): void {
		// Arrange.
		$value = '.5rem';

		// Act.
		$result = wc_clearance_sanitize_css_value( $value );

		// Assert.
		$this->assertSame( '.5rem', $result );
	}

	public function test_returns_valid_calc_with_em_value(): void {
		// Arrange.
		$value = 'calc(50% + 1em)';

		// Act.
		$result = wc_clearance_sanitize_css_value( $value );

		// Assert.
		$this->assertSame( 'calc(50% + 1em)', $result );
	}

	public function test_returns_valid_negative_px_value(): void {
		// Arrange.
		$value = '-2px';

		// Act.
		$result = wc_clearance_sanitize_css_value( $value );

		// Assert.
		$this->assertSame( '-2px', $result );
	}

	public function test_returns_empty_string_when_value_contains_semicolon_injection(): void {
		// Arrange.
		$value = '2px; color: red';

		// Act.
		$result = wc_clearance_sanitize_css_value( $value );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_returns_empty_string_when_value_starts_with_close_brace_injection(): void {
		// Arrange.
		$value = '} body { display: none';

		// Act.
		$result = wc_clearance_sanitize_css_value( $value );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_returns_empty_string_when_value_contains_close_brace_injection(): void {
		// Arrange.
		$value = '" ); } body { display: none; } /*"';

		// Act.
		$result = wc_clearance_sanitize_css_value( $value );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_returns_empty_string_for_non_string_value(): void {
		// Arrange.
		$value = 10;

		// Act.
		$result = wc_clearance_sanitize_css_value( $value );

		// Assert.
		$this->assertSame( '', $result );
	}
}
