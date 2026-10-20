<?php
/**
 * Tests for the four category tiles pattern.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_pattern_content;
use function OutletPro\init_taxonomies;
use function OutletPro\register_outlet_navigation_4_pattern;

class Test_Register_Outlet_Four_Tiles_Pattern extends WP_UnitTestCase {

	public function test_registers_the_pattern(): void {
		// Arrange.
		init_taxonomies();

		// Act.
		register_outlet_navigation_4_pattern();
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'outletpro/outlet-navigation-4' );

		// Assert.
		$this->assertSame( 'Outlet navigation with four category tiles', $pattern['title'] );
	}

	public function test_selects_the_top_four_categories_in_menu_order(): void {
		// Arrange.
		init_taxonomies();
		$category_ids = self::factory()->term->create_many( 5, array( 'taxonomy' => 'product_cat' ) );
		$product_ids  = self::factory()->post->create_many(
			2,
			array(
				'post_type'   => 'product',
				'post_status' => 'publish',
			)
		);
		wp_set_object_terms( $product_ids[0], $category_ids, 'product_cat' );
		wp_set_object_terms( $product_ids[1], array_slice( $category_ids, 0, 4 ), 'product_cat' );
		update_term_meta( $category_ids[0], 'order', 3 );
		update_term_meta( $category_ids[1], 'order', 2 );
		update_term_meta( $category_ids[2], 'order', 1 );
		update_term_meta( $category_ids[3], 'order', 0 );

		// Act.
		register_outlet_navigation_4_pattern();
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'outletpro/outlet-navigation-4' );
		$blocks  = parse_blocks( $pattern['content'] );
		$grid    = $blocks[0]['innerBlocks'][0];

		// Assert.
		$this->assertSame( array_reverse( array_slice( $category_ids, 0, 4 ) ), array_column( array_column( $grid['innerBlocks'], 'attrs' ), 'category' ) );
	}

	public function test_pads_missing_categories(): void {
		// Arrange.
		init_taxonomies();
		$category_id = self::factory()->term->create( array( 'taxonomy' => 'product_cat' ) );

		// Act.
		register_outlet_navigation_4_pattern();
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'outletpro/outlet-navigation-4' );
		$blocks  = parse_blocks( $pattern['content'] );
		$items   = array_column( array_column( $blocks[0]['innerBlocks'][0]['innerBlocks'], 'attrs' ), 'category' );

		// Assert.
		$this->assertCount( 4, $items );
		$this->assertContains( $category_id, $items );
		$this->assertContains( 0, $items );
	}

	public function test_uses_compact_stacked_mobile_tiles(): void {
		// Arrange.
		init_taxonomies();

		// Act.
		register_outlet_navigation_4_pattern();
		$blocks      = parse_blocks( get_pattern_content( 'outletpro/outlet-navigation-4' ) );
		$grid        = $blocks[0]['innerBlocks'][0];
		$tile_styles = array_column( array_column( $grid['innerBlocks'], 'attrs' ), 'style' );

		// Assert.
		$this->assertSame(
			array_fill(
				0,
				4,
				array(
					'dimensions' => array(
						'aspectRatio' => 'auto',
						'minHeight'   => '0',
					),
					'spacing'    => array(
						'padding' => array(
							'top'    => '0.5em',
							'bottom' => '0.5em',
						),
					),
				)
			),
			array_column( $tile_styles, '@mobile' )
		);
		$this->assertSame( 1, $grid['attrs']['style']['@mobile']['layout']['columnCount'] );
		$this->assertSame( 'clamp(12px, 6vw, 45px)', $grid['attrs']['style']['@mobile']['typography']['fontSize'] );
		$this->assertSame( 4, $grid['attrs']['layout']['columnCount'] );
		$this->assertSame( 2, $grid['attrs']['style']['@tablet']['layout']['columnCount'] );
		$this->assertSame( array_fill( 0, 4, array( 'aspectRatio' => '1' ) ), array_column( $tile_styles, 'dimensions' ) );
		$this->assertSame( array_fill( 0, 4, array( 'dimensions' => array( 'aspectRatio' => '1' ) ) ), array_column( $tile_styles, '@tablet' ) );
	}
}
