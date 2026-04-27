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
}
