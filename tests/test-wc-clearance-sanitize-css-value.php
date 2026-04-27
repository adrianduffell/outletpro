<?php
/**
 * Test the wc_clearance_sanitize_css_value function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\wc_clearance_sanitize_css_value;

class Test_Wc_Clearance_Sanitize_Css_Value extends WP_UnitTestCase {

	public function test_returns_valid_hex_color(): void {
		// Act.
		$result = wc_clearance_sanitize_css_value( '#FF0000' );

		// Assert.
		$this->assertSame( '#FF0000', $result );
	}

	public function test_trims_whitespace(): void {
		// Act.
		$result = wc_clearance_sanitize_css_value( '  #FF0000  ' );

		// Assert.
		$this->assertSame( '#FF0000', $result );
	}

	public function test_returns_empty_string_when_value_contains_semicolon(): void {
		// Act.
		$result = wc_clearance_sanitize_css_value( '#FF0000; color: red' );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_returns_empty_string_when_value_contains_open_brace(): void {
		// Act.
		$result = wc_clearance_sanitize_css_value( 'red { color: blue' );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_returns_empty_string_when_value_contains_close_brace(): void {
		// Act.
		$result = wc_clearance_sanitize_css_value( 'red } color: blue' );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_strips_html_tags(): void {
		// Act.
		$result = wc_clearance_sanitize_css_value( '<script>alert(1)</script>#FF0000' );

		// Assert.
		$this->assertSame( '#FF0000', $result );
	}

	public function test_returns_valid_rgba_value(): void {
		// Act.
		$result = wc_clearance_sanitize_css_value( 'rgba(255, 0, 0, 0.5)' );

		// Assert.
		$this->assertSame( 'rgba(255, 0, 0, 0.5)', $result );
	}

	public function test_returns_valid_calc_value(): void {
		// Act.
		$result = wc_clearance_sanitize_css_value( 'calc(100% - 2px)' );

		// Assert.
		$this->assertSame( 'calc(100% - 2px)', $result );
	}

	public function test_returns_valid_nested_calc_value(): void {
		// Act.
		$result = wc_clearance_sanitize_css_value( 'calc(1rem + calc(2px * 3))' );

		// Assert.
		$this->assertSame( 'calc(1rem + calc(2px * 3))', $result );
	}

	public function test_returns_valid_var_value(): void {
		// Act.
		$result = wc_clearance_sanitize_css_value( 'var(--my-spacing, 10px)' );

		// Assert.
		$this->assertSame( 'var(--my-spacing, 10px)', $result );
	}

	public function test_returns_valid_nested_var_value(): void {
		// Act.
		$result = wc_clearance_sanitize_css_value( 'var(--a, var(--b, 5px))' );

		// Assert.
		$this->assertSame( 'var(--a, var(--b, 5px))', $result );
	}

	public function test_returns_valid_clamp_value(): void {
		// Act.
		$result = wc_clearance_sanitize_css_value( 'clamp(0.75rem, 1vw, 1rem)' );

		// Assert.
		$this->assertSame( 'clamp(0.75rem, 1vw, 1rem)', $result );
	}

	public function test_returns_valid_color_mix_value(): void {
		// Act.
		$result = wc_clearance_sanitize_css_value( 'color-mix(in srgb, red 40%, blue)' );

		// Assert.
		$this->assertSame( 'color-mix(in srgb, red 40%, blue)', $result );
	}

	public function test_returns_valid_oklch_value(): void {
		// Act.
		$result = wc_clearance_sanitize_css_value( 'oklch(70% 0.15 200)' );

		// Assert.
		$this->assertSame( 'oklch(70% 0.15 200)', $result );
	}

	public function test_returns_valid_unitless_decimal_rem_value(): void {
		// Act.
		$result = wc_clearance_sanitize_css_value( '.5rem' );

		// Assert.
		$this->assertSame( '.5rem', $result );
	}

	public function test_returns_valid_calc_with_em_value(): void {
		// Act.
		$result = wc_clearance_sanitize_css_value( 'calc(50% + 1em)' );

		// Assert.
		$this->assertSame( 'calc(50% + 1em)', $result );
	}

	public function test_returns_valid_negative_px_value(): void {
		// Act.
		$result = wc_clearance_sanitize_css_value( '-2px' );

		// Assert.
		$this->assertSame( '-2px', $result );
	}

	public function test_returns_empty_string_when_value_contains_semicolon_injection(): void {
		// Act.
		$result = wc_clearance_sanitize_css_value( '2px; color: red' );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_returns_empty_string_when_value_starts_with_close_brace_injection(): void {
		// Act.
		$result = wc_clearance_sanitize_css_value( '} body { display: none' );

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_returns_empty_string_when_value_contains_close_brace_injection(): void {
		// Act.
		$result = wc_clearance_sanitize_css_value( '" ); } body { display: none; } /*"' );

		// Assert.
		$this->assertSame( '', $result );
	}
}
