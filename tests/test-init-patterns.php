<?php
/**
 * Test the init_patterns function.
 *
 * @package WC_Outlet
 */

use function WC_Outlet\deinit_patterns;
use function WC_Outlet\init_patterns;
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
				'name'  => 'wc-outlet',
				'label' => 'Outlet',
			),
			$categories,
		);
	}

	public function test_registers_outlet_sort_filter_pattern_only_for_wp_7_plus(): void {
		// Arrange.
		! \WP_Block_Patterns_Registry::get_instance()->is_registered( 'wc-outlet/outlet-sort-filter' )
			|| \WP_Block_Patterns_Registry::get_instance()->unregister( 'wc-outlet/outlet-sort-filter' );

		version_compare( get_bloginfo( 'version' ), '7.1', '<' )
			|| $this->fail( 'With WP 7.1 released, remove the version gate in init_patterns().' );

		// Act.
		init_patterns();

		// Assert.
		$this->assertSame(
			version_compare( get_bloginfo( 'version' ), '7.0', '>=' ),
			\WP_Block_Patterns_Registry::get_instance()->is_registered( 'wc-outlet/outlet-sort-filter' )
		);
	}
}
