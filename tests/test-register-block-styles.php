<?php
/**
 * Tests for register_block_styles().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_blocks;
use function OutletPro\register_block_styles;
use function OutletPro\register_outlet_tile_block;
use const OutletPro\OUTLET_PAGE_OPTION;

class Test_Register_Block_Styles extends WP_UnitTestCase {

	public function test_enqueues_tile_styles_when_a_tile_renders(): void {
		// Arrange.
		set_current_screen( 'front' );
		deinit_blocks();
		register_outlet_tile_block();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$category_id = self::factory()->term->create( array( 'taxonomy' => 'product_cat' ) );
		wp_dequeue_style( 'outletpro-tile-block' );
		wp_deregister_style( 'outletpro-tile-block' );
		add_filter( 'should_load_block_assets_on_demand', '__return_true' );
		register_block_styles();
		remove_filter( 'should_load_block_assets_on_demand', '__return_true' );

		// Act.
		do_blocks( '<!-- wp:outletpro/outlet-tile {"category":' . $category_id . '} /-->' );

		// Assert.
		$this->assertTrue( wp_style_is( 'outletpro-tile-block', 'enqueued' ) );
	}
}
