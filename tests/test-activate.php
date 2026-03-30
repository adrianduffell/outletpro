<?php
/**
 * Test the activate function.
 *
 * @package WC_Clearance
 */

use const WC_Clearance\ACTIVATED_AT_OPTION;
use const WC_Clearance\CLEARANCE_PAGE_OPTION;

class Test_Activate extends WP_UnitTestCase {

	public function test_creates_clearance_page_on_activation(): void {
		// Arrange.
		delete_option( CLEARANCE_PAGE_OPTION );

		// Act.
		do_action( 'activate_wc-clearance/wc-clearance.php' );

		// Assert.
		$this->assertGreaterThan( 0, get_option( CLEARANCE_PAGE_OPTION ) );
	}

	public function test_seeds_activated_at_option_on_activation(): void {
		// Arrange.
		delete_option( ACTIVATED_AT_OPTION );
		$before = time();

		// Act.
		do_action( 'activate_wc-clearance/wc-clearance.php' );

		// Assert.
		$after = time();
		$value = get_option( ACTIVATED_AT_OPTION );
		$this->assertGreaterThanOrEqual( $before, $value );
		$this->assertLessThanOrEqual( $after, $value );
	}

	public function test_updates_activated_at_option_on_reactivation(): void {
		// Arrange.
		$before = time();
		update_option( ACTIVATED_AT_OPTION, $before - 100 );

		// Act.
		do_action( 'activate_wc-clearance/wc-clearance.php' );

		// Assert.
		$after = time();
		$value = get_option( ACTIVATED_AT_OPTION );
		$this->assertGreaterThanOrEqual( $before, $value );
		$this->assertLessThanOrEqual( $after, $value );
	}
}
