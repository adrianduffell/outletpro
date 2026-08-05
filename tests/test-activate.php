<?php
/**
 * Test the activate function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\activate;
use const OutletPro\ACTIVATED_AT_OPTION;
use const OutletPro\OUTLET_PAGE_OPTION;

class Test_Activate extends WP_UnitTestCase {

	public function test_creates_outlet_page_on_activation(): void {
		// Arrange.
		delete_option( OUTLET_PAGE_OPTION );

		// Act.
		activate();

		// Assert.
		$this->assertGreaterThan( 0, get_option( OUTLET_PAGE_OPTION ) );
	}

	public function test_seeds_activated_at_option_on_activation(): void {
		// Arrange.
		delete_option( ACTIVATED_AT_OPTION );

		// Act.
		activate();

		// Assert.
		$this->assertNotFalse( get_option( ACTIVATED_AT_OPTION ) );
	}
}
