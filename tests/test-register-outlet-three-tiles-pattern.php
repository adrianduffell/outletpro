<?php
/**
 * Tests for the three category tiles pattern.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\register_outlet_navigation_3_pattern;

class Test_Register_Outlet_Three_Tiles_Pattern extends WP_UnitTestCase {

	public function test_registers_the_pattern(): void {
		// Act.
		register_outlet_navigation_3_pattern();
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'outletpro/outlet-navigation-3' );

		// Assert.
		$this->assertSame( 'Outlet navigation with three category tiles', $pattern['title'] );
	}

	public function test_selects_the_top_three_categories_in_menu_order(): void {
		// Arrange.
		$category_ids = self::factory()->term->create_many( 4, array( 'taxonomy' => 'product_cat' ) );
		$product_ids  = self::factory()->post->create_many(
			2,
			array(
				'post_type'   => 'product',
				'post_status' => 'publish',
			)
		);
		wp_set_object_terms( $product_ids[0], $category_ids, 'product_cat' );
		wp_set_object_terms( $product_ids[1], array_slice( $category_ids, 0, 3 ), 'product_cat' );
		update_term_meta( $category_ids[0], 'order', 2 );
		update_term_meta( $category_ids[1], 'order', 1 );
		update_term_meta( $category_ids[2], 'order', 0 );

		// Act.
		register_outlet_navigation_3_pattern();
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'outletpro/outlet-navigation-3' );
		$blocks  = parse_blocks( $pattern['content'] );

		// Assert.
		$this->assertSame( array_reverse( array_slice( $category_ids, 0, 3 ) ), array_column( array_column( $blocks[0]['innerBlocks'], 'attrs' ), 'category' ) );
		$this->assertSame( 3, $blocks[0]['attrs']['layout']['columnCount'] );
		$this->assertSame( 3, $blocks[0]['attrs']['style']['@tablet']['layout']['columnCount'] );
	}

	public function test_pads_missing_categories(): void {
		// Arrange.
		$category_id = self::factory()->term->create( array( 'taxonomy' => 'product_cat' ) );

		// Act.
		register_outlet_navigation_3_pattern();
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'outletpro/outlet-navigation-3' );
		$blocks  = parse_blocks( $pattern['content'] );

		// Assert.
		$this->assertSame( array( $category_id, 0, 0 ), array_column( array_column( $blocks[0]['innerBlocks'], 'attrs' ), 'category' ) );
	}
}
