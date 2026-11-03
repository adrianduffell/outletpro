<?php
/**
 * Tests for get_tuple_template_hook_priority().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_tuple_template_hook_priority;

class Test_Get_Tuple_Template_Hook_Priority extends WP_UnitTestCase {

	public function test_gets_the_hook_priority(): void {
		// Arrange.
		$tuple = array( 'woocommerce_single_product_summary', 0 );

		// Act.
		$priority = get_tuple_template_hook_priority( $tuple );

		// Assert.
		$this->assertSame( 0, $priority );
	}
}
