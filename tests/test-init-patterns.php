<?php
/**
 * Test the init_patterns function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_patterns;
use function OutletPro\init_patterns;

class Test_Init_Patterns extends WP_UnitTestCase {

	public function test_registers_outlet_block_pattern_category(): void {
		// Arrange.
		deinit_patterns();

		// Act.
		init_patterns();

		// Assert.
		$categories = \WP_Block_Pattern_Categories_Registry::get_instance()->get_all_registered();
		$this->assertContainsEquals(
			array(
				'name'  => 'outletpro',
				'label' => 'Outlet',
			),
			$categories,
		);
	}

	public function test_registers_navigation_patterns_without_filter_tiles(): void {
		// Arrange.
		deinit_patterns();

		// Act.
		init_patterns();

		// Assert.
		$patterns = \WP_Block_Patterns_Registry::get_instance();
		$this->assertFalse( $patterns->is_registered( 'outletpro/outlet-filter-tiles' ) );
		$this->assertTrue( $patterns->is_registered( 'outletpro/outlet-navigation-3' ) );
		$this->assertTrue( $patterns->is_registered( 'outletpro/outlet-navigation-4' ) );
		$this->assertTrue( $patterns->is_registered( 'outletpro/outlet-navigation-6' ) );
		$this->assertTrue( $patterns->is_registered( 'outletpro/outlet-navigation-price-tiers' ) );
	}

	public function test_registers_outlet_sort_filter_pattern(): void {
		// Arrange.
		deinit_patterns();

		// Act.
		init_patterns();

		// Assert.
		$this->assertTrue(
			\WP_Block_Patterns_Registry::get_instance()->is_registered( 'outletpro/outlet-sort-filter' )
		);
	}
}
