<?php
/**
 * Tests for the Outlet Badge row block pattern.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_patterns;
use function OutletPro\register_outlet_badge_row_pattern;

class Test_Register_Outlet_Badge_Row_Pattern extends WP_UnitTestCase {

	public function test_pattern_is_registered_with_expected_properties(): void {
		// Arrange.
		deinit_patterns();

		// Act.
		register_outlet_badge_row_pattern();
		$pattern = \WP_Block_Patterns_Registry::get_instance()->get_registered( 'outletpro/outlet-badge-row' );

		// Assert.
		$this->assertSame( 'Outlet Badge row', $pattern['title'] );
		$this->assertSame( 'Displays the outlet badge in a row, aligned left.', $pattern['description'] );
		$this->assertSame( array( 'outletpro' ), $pattern['categories'] );
	}

	public function test_pattern_content_contains_a_left_aligned_outlet_badge(): void {
		// Arrange.
		deinit_patterns();
		register_outlet_badge_row_pattern();

		// Act.
		$pattern = \WP_Block_Patterns_Registry::get_instance()->get_registered( 'outletpro/outlet-badge-row' );
		$blocks  = array_values(
			array_filter(
				parse_blocks( $pattern['content'] ),
				fn( $block ) => null !== $block['blockName']
			)
		);

		// Assert.
		$this->assertCount( 1, $blocks );
		$this->assertSame( 'outletpro/outlet-group', $blocks[0]['blockName'] );
		$this->assertSame(
			array(
				'type'           => 'flex',
				'flexWrap'       => 'nowrap',
				'justifyContent' => 'left',
			),
			$blocks[0]['attrs']['layout']
		);
		$this->assertSame( 'outlet-badge-row', $blocks[0]['attrs']['className'] );
		$this->assertCount( 1, $blocks[0]['innerBlocks'] );
		$this->assertSame( 'outletpro/outlet-badge', $blocks[0]['innerBlocks'][0]['blockName'] );
	}
}
