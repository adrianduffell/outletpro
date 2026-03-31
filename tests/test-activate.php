<?php
/**
 * Test the activate function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\activate;
use const WC_Clearance\ACTIVATED_AT_OPTION;
use const WC_Clearance\CLEARANCE_PAGE_OPTION;

class Test_Activate extends WP_UnitTestCase {

	public function test_creates_clearance_page_on_activation(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );

		// Act.
		activate();

		// Assert.
		$this->assertGreaterThan( 0, get_option( CLEARANCE_PAGE_OPTION ) );
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
