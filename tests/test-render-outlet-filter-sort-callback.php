<?php
/**
 * Tests for render_outlet_filter_sort_callback().
 *
 * @package WC_Outlet
 */

use function WC_Outlet\deinit_blocks;
use function WC_Outlet\init_blocks;
use function WC_Outlet\register_outlet_filter_sort_block;
use function WC_Outlet\render_outlet_filter_sort_callback;

class Test_Render_Outlet_Filter_Sort_Callback extends WP_UnitTestCase {

	public function test_filter_sort_is_registered_after_init_blocks(): void {
		// Arrange.
		deinit_blocks();

		// Act.
		init_blocks();

		// Assert.
		$this->assertTrue( \WP_Block_Type_Registry::get_instance()->is_registered( 'wc-outlet/outlet-filter-sort' ) );
	}

	public function test_returns_catalog_sorting_block_render(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_filter_sort_block();
		$block = new WP_Block(
			array(
				'blockName'    => 'wc-outlet/outlet-filter-sort',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			),
			array()
		);
		$expected = render_block(
			array(
				'blockName'    => 'woocommerce/catalog-sorting',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			)
		);

		// Act.
		$result = render_outlet_filter_sort_callback( array(), '', $block );

		// Assert.
		$this->assertSame( $expected, $result );
	}
}
