<?php
/**
 * Tests for deinit_blocks().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_blocks;

class Test_Deinit_Blocks extends WP_UnitTestCase {

	public function test_block_is_unregistered_after_deinit_blocks(): void {
		// Arrange.
		register_block_type(
			'outletpro/foo',
			array(
				'render_callback' => '__return_empty_string',
			)
		);
		$this->assertTrue( \WP_Block_Type_Registry::get_instance()->is_registered( 'outletpro/foo' ) );

		// Act.
		deinit_blocks();

		// Assert.
		$this->assertFalse( \WP_Block_Type_Registry::get_instance()->is_registered( 'outletpro/foo' ) );
	}

	public function test_safely_handles_block_not_registered(): void {
		// Arrange.
		$this->assertFalse( \WP_Block_Type_Registry::get_instance()->is_registered( 'outletpro/bar' ) );

		// Act.
		deinit_blocks();

		// Assert.
		$this->assertFalse( \WP_Block_Type_Registry::get_instance()->is_registered( 'outletpro/bar' ) );
	}
}
