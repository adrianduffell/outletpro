<?php
/**
 * Test the safe_get_site_key function.
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\safe_get_site_key;

class Test_Safe_Get_Site_Key extends WP_UnitTestCase {

	public function test_returns_localhost_without_port(): void {
		// Arrange.
		add_filter(
			'home_url',
			fn(): string => 'http://localhost:8080',
			10,
			0
		);

		// Act.
		$site_key = safe_get_site_key();

		// Assert.
		$this->assertSame( 'localhost', $site_key );
	}

	public function test_sanitizes_domain_name(): void {
		// Arrange.
		add_filter(
			'home_url',
			fn(): string => 'https://Example.COM',
			10,
			0
		);

		// Act.
		$site_key = safe_get_site_key();

		// Assert.
		$this->assertSame( 'example_com', $site_key );
	}

	public function test_keeps_last_80_characters_of_long_site_key(): void {
		// Arrange.
		$url = 'https://' . str_repeat( 'a', 40 ) . '.' . str_repeat( 'b', 40 ) . '.com';
		add_filter(
			'home_url',
			fn(): string => $url,
			10,
			0
		);

		// Act.
		$site_key = safe_get_site_key();

		// Assert.
		$this->assertSame( str_repeat( 'a', 35 ) . '_' . str_repeat( 'b', 40 ) . '_com', $site_key );
		$this->assertSame( 80, strlen( $site_key ) );
	}

	public function test_returns_fallback_for_url_without_domain(): void {
		// Arrange.
		add_filter(
			'home_url',
			fn(): string => 'invalid-url',
			10,
			0
		);

		// Act.
		$site_key = safe_get_site_key();

		// Assert.
		$this->assertSame( 'invalid', $site_key );
	}

	public function test_returns_fallback_for_non_string_home_url(): void {
		// Arrange.
		add_filter(
			'home_url',
			fn(): array => array( 'https://example.com' ),
			10,
			0
		);

		// Act.
		$site_key = safe_get_site_key();

		// Assert.
		$this->assertSame( 'invalid', $site_key );
	}

	public function test_returns_fallback_for_non_string_sanitized_key(): void {
		// Arrange.
		add_filter(
			'home_url',
			fn(): string => 'https://example.com',
			10,
			0
		);
		add_filter(
			'sanitize_key',
			fn(): array => array( 'example_com' ),
			10,
			0
		);

		// Act.
		$site_key = safe_get_site_key();

		// Assert.
		$this->assertSame( 'invalid', $site_key );
	}

	public function test_returns_fallback_for_empty_sanitized_key(): void {
		// Arrange.
		add_filter(
			'home_url',
			fn(): string => 'https://!',
			10,
			0
		);

		// Act.
		$site_key = safe_get_site_key();

		// Assert.
		$this->assertSame( 'invalid', $site_key );
	}

	public function test_returns_fallback_when_url_filter_throws(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		add_filter(
			'home_url',
			function (): string {
				throw new RuntimeException( 'Simulated home URL failure.' );
			},
			10,
			0
		);

		// Act.
		$site_key = safe_get_site_key();

		// Assert.
		$this->assertSame( 'invalid', $site_key );
	}
}
