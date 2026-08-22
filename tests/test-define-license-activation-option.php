<?php
/**
 * Test the define_license_activation_option function.
 *
 * @package OutletPro
 * @group license
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\define_license_activation_option;

class Test_Define_License_Activation_Option extends WP_UnitTestCase {

	public function test_defines_activation_option_with_unique_suffix(): void {
		// Arrange.
		$pattern = '/^outletpro_license_activation_.{8}$/';

		// Act.
		define_license_activation_option();
		$string_constants   = array_filter( get_defined_constants(), 'is_string' );
		$activation_options = preg_grep( $pattern, $string_constants );

		// Assert.
		$this->assertCount( 1, $activation_options );
	}
}
