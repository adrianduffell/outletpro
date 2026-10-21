<?php
/**
 * Tests for register_outlet_tiles_block().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_blocks;
use function OutletPro\register_outlet_tiles_block;

class Test_Register_Outlet_Tiles_Block extends WP_UnitTestCase {

	public function test_does_not_offer_parent_color_controls(): void {
		// Arrange.
		deinit_blocks();

		// Act.
		register_outlet_tiles_block();
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'outletpro/outlet-tiles' );

		// Assert.
		$this->assertFalse( block_has_support( $block_type, array( 'color', 'text' ) ) );
		$this->assertFalse( block_has_support( $block_type, array( 'color', 'background' ) ) );
	}

	public function test_registers_outlet_tiles_block(): void {
		// Arrange.
		deinit_blocks();

		// Act.
		register_outlet_tiles_block();

		// Assert.
		$this->assertTrue( WP_Block_Type_Registry::get_instance()->is_registered( 'outletpro/outlet-tiles' ) );
	}
}
