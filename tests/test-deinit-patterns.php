<?php
/**
 * Tests for deinit_patterns().
 *
 * @package OutletPro
 */

use function OutletPro\deinit_patterns;
use function OutletPro\init_patterns;

class Test_Deinit_Patterns extends WP_UnitTestCase {

	public function test_pattern_is_unregistered_after_deinit_patterns(): void {
		// Arrange.
		deinit_patterns();
		init_patterns();
		$this->assertTrue( \WP_Block_Patterns_Registry::get_instance()->is_registered( 'wc-outlet/outlet-filter-tiles' ) );
		$this->assertTrue( \WP_Block_Pattern_Categories_Registry::get_instance()->is_registered( 'wc-outlet' ) );

		// Act.
		deinit_patterns();

		// Assert.
		$this->assertFalse( \WP_Block_Patterns_Registry::get_instance()->is_registered( 'wc-outlet/outlet-filter-tiles' ) );
		$this->assertFalse( \WP_Block_Pattern_Categories_Registry::get_instance()->is_registered( 'wc-outlet' ) );
	}

	public function test_safely_handles_pattern_not_registered(): void {
		// Arrange.
		deinit_patterns();
		$this->assertFalse( \WP_Block_Patterns_Registry::get_instance()->is_registered( 'wc-outlet/outlet-filter-tiles' ) );
		$this->assertFalse( \WP_Block_Pattern_Categories_Registry::get_instance()->is_registered( 'wc-outlet' ) );

		// Act.
		deinit_patterns();

		// Assert.
		$this->assertFalse( \WP_Block_Patterns_Registry::get_instance()->is_registered( 'wc-outlet/outlet-filter-tiles' ) );
		$this->assertFalse( \WP_Block_Pattern_Categories_Registry::get_instance()->is_registered( 'wc-outlet' ) );
	}
}
