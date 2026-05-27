<?php
/**
 * Test the init_patterns function.
 *
 * @package WC_Outlet
 */

use function WC_Outlet\init_patterns;

class Test_Init_Patterns extends WP_UnitTestCase {

	public function test_registers_outlet_block_pattern_category(): void {
		// Arrange.
		unregister_block_pattern_category( 'wc-outlet' );

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
}
