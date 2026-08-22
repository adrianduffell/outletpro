<?php
/**
 * Test the get_license_activation_site_hash function.
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_license_activation_site_hash;
use const OutletPro\LICENSE_ACTIVATION_OPTION_PREFIX;

class Test_Get_License_Activation_Site_Hash extends WP_UnitTestCase {

	public function test_hashes_site_url_without_its_scheme(): void { //phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		add_filter(
			'home_url',
			function (): string {
				return 'http://example.com/';
			}
		);

		// Act.
		$site_hash = get_license_activation_site_hash();

		// Assert.
		$this->assertSame( 'cbd823b2', $site_hash );
		$this->assertSame( 'outletpro_license_activation_cbd823b2', LICENSE_ACTIVATION_OPTION_PREFIX . $site_hash );
	}
}
