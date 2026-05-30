<?php
/**
 * Tests for deinit_patterns().
 *
 * @package WC_Outlet
 */

use function WC_Outlet\deinit_patterns;
use function WC_Outlet\register_outlet_filter_tiles_pattern;

class Test_Deinit_Patterns extends WP_UnitTestCase {

	public function test_pattern_is_unregistered_after_deinit_patterns(): void {
		// Arrange.
		if ( ! \WP_Block_Patterns_Registry::get_instance()->is_registered( 'wc-outlet/outlet-filter-tiles' ) ) {
			register_outlet_filter_tiles_pattern();
		}
		$this->assertTrue( \WP_Block_Patterns_Registry::get_instance()->is_registered( 'wc-outlet/outlet-filter-tiles' ) );

		// Act.
		deinit_patterns();

		// Assert.
		$this->assertFalse( \WP_Block_Patterns_Registry::get_instance()->is_registered( 'wc-outlet/outlet-filter-tiles' ) );
	}

	public function test_safely_handles_pattern_not_registered(): void {
		// Arrange.
		if ( \WP_Block_Patterns_Registry::get_instance()->is_registered( 'wc-outlet/outlet-filter-tiles' ) ) {
			unregister_block_pattern( 'wc-outlet/outlet-filter-tiles' );
		}
		$this->assertFalse( \WP_Block_Patterns_Registry::get_instance()->is_registered( 'wc-outlet/outlet-filter-tiles' ) );

		// Act.
		deinit_patterns();

		// Assert.
		$this->assertFalse( \WP_Block_Patterns_Registry::get_instance()->is_registered( 'wc-outlet/outlet-filter-tiles' ) );
	}
}
