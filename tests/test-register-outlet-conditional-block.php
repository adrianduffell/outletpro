<?php
/**
 * Tests for register_outlet_conditional_block().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_blocks;
use function OutletPro\register_outlet_conditional_block;

class Test_Register_Outlet_Conditional_Block extends WP_UnitTestCase {

	public function test_registers_outlet_conditional_block(): void {
		// Arrange.
		deinit_blocks();

		// Act.
		register_outlet_conditional_block();

		// Assert.
		$this->assertTrue( \WP_Block_Type_Registry::get_instance()->is_registered( 'outletpro/outlet-conditional' ) );
	}
}
