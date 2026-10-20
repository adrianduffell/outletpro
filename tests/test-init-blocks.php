<?php
/**
 * Tests for init_blocks().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_blocks;
use function OutletPro\init_blocks;

class Test_Init_Blocks extends WP_UnitTestCase {

	public function test_registers_blocks(): void {
		// Arrange.
		deinit_blocks();

		// Act.
		init_blocks();
		$registry = WP_Block_Type_Registry::get_instance();

		// Assert.
		$this->assertTrue( $registry->is_registered( 'outletpro/outlet-conditional' ) );
		$this->assertTrue( $registry->is_registered( 'outletpro/outlet-badge' ) );
		$this->assertTrue( $registry->is_registered( 'outletpro/outlet-message' ) );
		$this->assertTrue( $registry->is_registered( 'outletpro/outlet-menu' ) );
		$this->assertTrue( $registry->is_registered( 'outletpro/outlet-tiles' ) );
		$this->assertTrue( $registry->is_registered( 'outletpro/outlet-tile' ) );
	}
}
