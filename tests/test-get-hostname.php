<?php
/**
 * Tests for get_hostname().
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_hostname;

class Test_Get_Hostname extends WP_UnitTestCase {

	public function test_returns_domain_name(): void {
		// Arrange.
		add_filter( 'home_url', fn(): string => 'https://example.com', 10, 0 );

		// Act.
		$result = get_hostname();

		// Assert.
		$this->assertSame( 'example.com', $result );
	}

	public function test_returns_subdomain_name(): void {
		// Arrange.
		add_filter( 'home_url', fn(): string => 'https://store.example.com/shop', 10, 0 );

		// Act.
		$result = get_hostname();

		// Assert.
		$this->assertSame( 'store.example.com', $result );
	}

	public function test_returns_localhost_without_port(): void {
		// Arrange.
		add_filter( 'home_url', fn(): string => 'http://localhost:1111/wordpress', 10, 0 );

		// Act.
		$result = get_hostname();

		// Assert.
		$this->assertSame( 'localhost', $result );
	}

	public function test_returns_ipv4_address_without_port(): void {
		// Arrange.
		add_filter( 'home_url', fn(): string => 'http://127.0.0.1:8080', 10, 0 );

		// Act.
		$result = get_hostname();

		// Assert.
		$this->assertSame( '127.0.0.1', $result );
	}

	public function test_returns_bracketed_ipv6_address_without_port(): void {
		// Arrange.
		add_filter( 'home_url', fn(): string => 'http://[::1]:8080', 10, 0 );

		// Act.
		$result = get_hostname();

		// Assert.
		$this->assertSame( '[::1]', $result );
	}

	public function test_preserves_hostname_case(): void {
		// Arrange.
		add_filter( 'home_url', fn(): string => 'https://STORE.EXAMPLE.COM', 10, 0 );

		// Act.
		$result = get_hostname();

		// Assert.
		$this->assertSame( 'STORE.EXAMPLE.COM', $result );
	}

	public function test_throws_for_hostless_url(): void {
		// Arrange.
		add_filter( 'home_url', fn(): string => '/wordpress', 10, 0 );

		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Hostname could not be retrieved' );

		// Act.
		get_hostname();
	}

	public function test_throws_for_malformed_url(): void {
		// Arrange.
		add_filter( 'home_url', fn(): string => 'http://:', 10, 0 );

		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Hostname could not be retrieved' );

		// Act.
		get_hostname();
	}
}
