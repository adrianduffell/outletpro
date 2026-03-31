<?php
/**
 * Test the seed_activated_at_option function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\seed_activated_at_option;
use const WC_Clearance\ACTIVATED_AT_OPTION;

class Test_Seed_Activated_At_Option extends WP_UnitTestCase {

	public function test_seeds_activated_at_option_with_current_time(): void {
		// Arrange.
		delete_option( ACTIVATED_AT_OPTION );
		$before = time();

		// Act.
		seed_activated_at_option();

		// Assert.
		$after = time();
		$value = get_option( ACTIVATED_AT_OPTION );
		$this->assertGreaterThanOrEqual( $before, $value );
		$this->assertLessThanOrEqual( $after, $value );
	}

	public function test_overwrites_existing_option_with_current_time(): void {
		// Arrange.
		$before = time();
		update_option( ACTIVATED_AT_OPTION, $before - 100 );

		// Act.
		seed_activated_at_option();

		// Assert.
		$after = time();
		$value = get_option( ACTIVATED_AT_OPTION );
		$this->assertGreaterThanOrEqual( $before, $value );
		$this->assertLessThanOrEqual( $after, $value );
	}
}
