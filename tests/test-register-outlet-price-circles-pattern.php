<?php
/**
 * Tests for the price tiers with circles pattern.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\init_taxonomies;
use function OutletPro\register_outlet_navigation_price_tiers_pattern;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Register_Outlet_Price_Circles_Pattern extends WP_UnitTestCase {

	public function test_registers_the_pattern(): void {
		// Arrange.
		init_taxonomies();

		// Act.
		register_outlet_navigation_price_tiers_pattern();
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'outletpro/outlet-navigation-price-tiers' );

		// Assert.
		$this->assertSame( 'Outlet navigation with circular price tier tiles', $pattern['title'] );
	}

	public function test_selects_the_four_lowest_price_tiers(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		$prices   = array( 100, 10, 200, 50, 25 );
		$tier_ids = array_combine(
			$prices,
			array_map(
				static fn( int $price ): int => self::factory()->term->create(
					array(
						'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
						'slug'     => 'outlet-price-' . $price,
					)
				),
				$prices
			)
		);

		// Act.
		register_outlet_navigation_price_tiers_pattern();
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'outletpro/outlet-navigation-price-tiers' );
		$blocks  = parse_blocks( $pattern['content'] );
		$grid    = $blocks[0]['innerBlocks'][0];

		// Assert.
		$this->assertSame(
			array( $tier_ids[10], $tier_ids[25], $tier_ids[50], $tier_ids[100] ),
			array_column( array_column( $grid['innerBlocks'], 'attrs' ), 'category' )
		);
	}

	public function test_preserves_price_tile_content(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		$term = wp_insert_term( 'outlet-price-25', OUTLET_SEARCH_INDEX_TAXONOMY );

		// Act.
		register_outlet_navigation_price_tiers_pattern();
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'outletpro/outlet-navigation-price-tiers' );
		$blocks  = parse_blocks( $pattern['content'] );
		$blocks  = $blocks[0]['innerBlocks'];

		// Assert.
		$this->assertSame( array( $term['term_id'], 0, 0, 0 ), array_column( array_column( $blocks[0]['innerBlocks'], 'attrs' ), 'category' ) );
		$this->assertSame( 'outletpro/outlet-menu', $blocks[1]['blockName'] );
	}

	public function test_menu_omits_price_tiers(): void {
		// Arrange.
		init_taxonomies();
		truncate_outlet_search_index_taxonomy();
		$price    = self::factory()->term->create(
			array(
				'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
				'slug'     => 'outlet-price-25',
			)
		);
		$discount = self::factory()->term->create(
			array(
				'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
				'slug'     => 'outlet-discount-50',
			)
		);
		$category = self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'parent'   => 0,
			)
		);

		// Act.
		register_outlet_navigation_price_tiers_pattern();
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'outletpro/outlet-navigation-price-tiers' );
		$blocks  = parse_blocks( $pattern['content'] );
		$items   = $blocks[0]['innerBlocks'][1]['attrs']['items'];

		// Assert.
		$this->assertNotContains( $price, $items );
		$this->assertContains( $discount, $items );
		$this->assertContains( $category, $items );
	}
}
