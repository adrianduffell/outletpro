<?php
/**
 * Tests for register_outlet_tile_block().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_blocks;
use function OutletPro\register_outlet_tile_block;

class Test_Register_Outlet_Tile_Block extends WP_UnitTestCase {

	public function test_registers_outlet_tile_block(): void {
		// Arrange.
		deinit_blocks();

		// Act.
		register_outlet_tile_block();

		// Assert.
		$this->assertTrue( WP_Block_Type_Registry::get_instance()->is_registered( 'outletpro/outlet-tile' ) );
	}

	public function test_defaults_tiles_to_badge_presets(): void {
		// Arrange.
		deinit_blocks();
		wp_clean_theme_json_cache();

		// Act.
		register_outlet_tile_block();
		$colors = wp_get_global_styles( array( 'color' ), array( 'block_name' => 'outletpro/outlet-tile' ) );

		// Assert.
		$this->assertSame(
			array(
				'background' => 'var(--wp--preset--color--outletpro-badge-bg-color)',
				'text'       => 'var(--wp--preset--color--outletpro-badge-text-color)',
			),
			$colors
		);
	}
}
