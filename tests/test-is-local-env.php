<?php
/**
 * Tests for is_local_env().
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\is_local_env;

class Test_Is_Local_Env extends WP_UnitTestCase {

	public function test_returns_true_for_localhost(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		add_filter(
			'site_url',
			function (): string {
				return 'http://localhost:8888/wordpress';
			}
		);

		// Act.
		$result = is_local_env();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_returns_true_for_localhost_subdomain(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		add_filter(
			'site_url',
			function (): string {
				return 'https://shop.localhost';
			}
		);

		// Act.
		$result = is_local_env();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_returns_true_for_ipv4_loopback_address(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		add_filter(
			'site_url',
			function (): string {
				return 'http://127.0.0.1';
			}
		);

		// Act.
		$result = is_local_env();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_returns_true_for_ipv6_loopback_address(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		add_filter(
			'site_url',
			function (): string {
				return 'http://[::1]:8080';
			}
		);

		// Act.
		$result = is_local_env();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_returns_true_for_local_domain(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		add_filter(
			'site_url',
			function (): string {
				return 'https://outlet-pro.local';
			}
		);

		// Act.
		$result = is_local_env();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_returns_true_for_test_domain_case_insensitively(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		add_filter(
			'site_url',
			function (): string {
				return 'https://Outlet-Pro.TEST';
			}
		);

		// Act.
		$result = is_local_env();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_returns_false_for_production_domain(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		add_filter(
			'site_url',
			function (): string {
				return 'https://outletpro.zip';
			}
		);

		// Act.
		$result = is_local_env();

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_returns_false_when_local_is_not_the_domain_suffix(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		add_filter(
			'site_url',
			function (): string {
				return 'https://local.example.com';
			}
		);

		// Act.
		$result = is_local_env();

		// Assert.
		$this->assertFalse( $result );
	}
}
