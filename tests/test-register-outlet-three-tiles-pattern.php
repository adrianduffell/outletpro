<?php
/**
 * Tests for the three category tiles pattern.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_pattern_content;
use function OutletPro\init_taxonomies;
use function OutletPro\register_outlet_navigation_3_pattern;
use const OutletPro\OUTLET_BADGE_BG_COLOR_OPTION;
use const OutletPro\OUTLET_BADGE_TEXT_COLOR_OPTION;

class Test_Register_Outlet_Three_Tiles_Pattern extends WP_UnitTestCase {

	public function test_applies_badge_colors_to_each_tile(): void {
		// Arrange.
		init_taxonomies();
		update_option( OUTLET_BADGE_BG_COLOR_OPTION, '#ffff00' );
		update_option( OUTLET_BADGE_TEXT_COLOR_OPTION, '#123456' );

		// Act.
		register_outlet_navigation_3_pattern();
		$blocks      = parse_blocks( get_pattern_content( 'outletpro/outlet-navigation-3' ) );
		$grid        = $blocks[0]['innerBlocks'][0];
		$tile_styles = array_column( array_column( $grid['innerBlocks'], 'attrs' ), 'style' );

		// Assert.
		$this->assertArrayNotHasKey( 'color', $grid['attrs']['style'] );
		$this->assertSame( array_fill( 0, 3, '#123456' ), array_column( array_column( $tile_styles, 'color' ), 'text' ) );
		$this->assertSame( array_fill( 0, 3, '#ffff00' ), array_column( array_column( $tile_styles, 'color' ), 'background' ) );
	}

	public function test_leaves_unset_colors_unstyled(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Exercise missing and empty options with the same assertions.
		foreach ( array( null, '' ) as $color ) {
			// Arrange.
			init_taxonomies();
			delete_option( OUTLET_BADGE_BG_COLOR_OPTION );
			delete_option( OUTLET_BADGE_TEXT_COLOR_OPTION );
			if ( null !== $color ) {
				update_option( OUTLET_BADGE_BG_COLOR_OPTION, $color );
				update_option( OUTLET_BADGE_TEXT_COLOR_OPTION, $color );
			}

			// Act.
			register_outlet_navigation_3_pattern();
			$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'outletpro/outlet-navigation-3' );
			$blocks  = parse_blocks( $pattern['content'] );
			$grid    = $blocks[0]['innerBlocks'][0];

			// Assert.
			$this->assertArrayNotHasKey( 'color', $grid['attrs']['style'] );
			$this->assertEmpty( $grid['innerBlocks'][0]['attrs']['style']['color']['text'] );
			$this->assertEmpty( $grid['innerBlocks'][0]['attrs']['style']['color']['background'] );
			$this->assertStringNotContainsString( 'has-text-color', $pattern['content'] );
			$this->assertStringNotContainsString( 'color:', $pattern['content'] );
		}
	}

	public function test_registers_the_pattern(): void {
		// Arrange.
		init_taxonomies();

		// Act.
		register_outlet_navigation_3_pattern();
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'outletpro/outlet-navigation-3' );
		$blocks  = parse_blocks( get_pattern_content( 'outletpro/outlet-navigation-3' ) );

		// Assert.
		$this->assertSame( 'Outlet navigation with three category tiles', $pattern['title'] );
		$this->assertSame( 'outletpro/outlet-navigation-3', $blocks[0]['attrs']['metadata']['patternName'] );
	}

	public function test_selects_the_top_three_categories_in_menu_order(): void {
		// Arrange.
		init_taxonomies();
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
		$blocks  = $blocks[0]['innerBlocks'];

		// Assert.
		$this->assertSame( array_reverse( array_slice( $category_ids, 0, 3 ) ), array_column( array_column( $blocks[0]['innerBlocks'], 'attrs' ), 'category' ) );
		$this->assertSame( 3, $blocks[0]['attrs']['layout']['columnCount'] );
		$this->assertSame( 3, $blocks[0]['attrs']['style']['@tablet']['layout']['columnCount'] );
	}

	public function test_pads_missing_categories(): void {
		// Arrange.
		init_taxonomies();
		$category_id = self::factory()->term->create( array( 'taxonomy' => 'product_cat' ) );

		// Act.
		register_outlet_navigation_3_pattern();
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'outletpro/outlet-navigation-3' );
		$blocks  = parse_blocks( $pattern['content'] );
		$blocks  = $blocks[0]['innerBlocks'];
		$items   = array_column( array_column( $blocks[0]['innerBlocks'], 'attrs' ), 'category' );

		// Assert.
		$this->assertCount( 3, $items );
		$this->assertContains( $category_id, $items );
		$this->assertContains( 0, $items );
	}

	public function test_uses_compact_stacked_mobile_tiles(): void {
		// Arrange.
		init_taxonomies();

		// Act.
		register_outlet_navigation_3_pattern();
		$blocks      = parse_blocks( get_pattern_content( 'outletpro/outlet-navigation-3' ) );
		$grid        = $blocks[0]['innerBlocks'][0];
		$tile_styles = array_column( array_column( $grid['innerBlocks'], 'attrs' ), 'style' );

		// Assert.
		$this->assertSame(
			array_fill(
				0,
				3,
				array(
					'dimensions' => array( 'minHeight' => '0' ),
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
		$this->assertSame(
			array_fill(
				0,
				3,
				array(
					'top'    => '2em',
					'right'  => '0.75em',
					'bottom' => '2em',
					'left'   => '0.75em',
				)
			),
			array_column( array_column( $tile_styles, 'spacing' ), 'padding' )
		);
	}
}
