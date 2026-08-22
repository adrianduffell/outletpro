<?php
/**
 * Test the get_site_hash function.
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_site_hash;

class Test_Get_Site_Hash extends WP_UnitTestCase {

	public function test_returns_truncated_sha256_hash_without_http_scheme(): void {
		// Arrange.
		add_filter(
			'home_url',
			fn(): string => 'http://example.com/',
			10,
			0
		);

		// Act.
		$site_hash = get_site_hash();

		// Assert.
		$this->assertSame( '73d986e00906', $site_hash );
	}

	public function test_requests_root_home_url_over_http(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		$requested_path   = null;
		$requested_scheme = null;
		add_filter(
			'home_url',
			function ( $url, $path, $scheme ) use ( &$requested_path, &$requested_scheme ): string {
				$requested_path   = $path;
				$requested_scheme = $scheme;

				return 'http://example.com/';
			},
			10,
			3
		);

		// Act.
		get_site_hash();

		// Assert.
		$this->assertSame( '/', $requested_path );
		$this->assertSame( 'http', $requested_scheme );
	}
}
