<?php
/**
 * Tests for get_tuple_template_hook_name().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_tuple_template_hook_name;

class Test_Get_Tuple_Template_Hook_Name extends WP_UnitTestCase {

	public function test_gets_the_hook_name(): void {
		// Arrange.
		$tuple = array( 'woocommerce_product_meta_start', 1 );

		// Act.
		$name = get_tuple_template_hook_name( $tuple );

		// Assert.
		$this->assertSame( 'woocommerce_product_meta_start', $name );
	}
}
