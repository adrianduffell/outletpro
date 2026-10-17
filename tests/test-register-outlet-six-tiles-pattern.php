<?php
/**
 * Tests for the outlet navigation with six category tiles block pattern.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\register_outlet_navigation_6_pattern;
use function OutletPro\truncate_outlet_search_index_taxonomy;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Register_Outlet_Six_Tiles_Pattern extends WP_UnitTestCase {

	public function test_selected_categories_follow_woocommerce_display_order(): void {
		// Arrange.
		$first_category_id  = self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'name'     => 'First category',
			)
		);
		$second_category_id = self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'name'     => 'Second category',
			)
		);
		$third_category_id  = self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'name'     => 'Third category',
			)
		);
		$fourth_category_id = self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'name'     => 'Fourth category',
			)
		);
		$product_ids        = self::factory()->post->create_many(
			4,
			array(
				'post_type'   => 'product',
				'post_status' => 'publish',
			)
		);
		wp_set_object_terms( $product_ids[0], array( $first_category_id, $second_category_id, $third_category_id, $fourth_category_id ), 'product_cat' );
		wp_set_object_terms( $product_ids[1], array( $first_category_id, $second_category_id, $third_category_id ), 'product_cat' );
		wp_set_object_terms( $product_ids[2], array( $first_category_id, $second_category_id ), 'product_cat' );
		wp_set_object_terms( $product_ids[3], array( $first_category_id ), 'product_cat' );
		update_term_meta( $third_category_id, 'order', 0 );
		update_term_meta( $second_category_id, 'order', 1 );
		update_term_meta( $first_category_id, 'order', 2 );
		update_term_meta( $fourth_category_id, 'order', 3 );

		// Act.
		register_outlet_navigation_6_pattern();
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'outletpro/outlet-navigation-6' );
		$blocks  = parse_blocks( $pattern['content'] );
		$blocks  = $blocks[0]['innerBlocks'];

		// Assert.
		$this->assertSame(
			array( $third_category_id, $second_category_id, $first_category_id, $fourth_category_id, 0, 0 ),
			array_column( array_column( $blocks[0]['innerBlocks'], 'attrs' ), 'category' )
		);
	}

	public function test_tiles_exclude_child_categories(): void {
		// Arrange.
		$parent_category_id = self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'name'     => 'Parent category',
			)
		);
		$child_category_id  = self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'name'     => 'Child category',
				'parent'   => $parent_category_id,
			)
		);
		$product_id         = self::factory()->post->create(
			array(
				'post_type'   => 'product',
				'post_status' => 'publish',
			)
		);
		wp_set_object_terms( $product_id, array( $parent_category_id, $child_category_id ), 'product_cat' );

		// Act.
		register_outlet_navigation_6_pattern();
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'outletpro/outlet-navigation-6' );
		$blocks  = parse_blocks( $pattern['content'] );
		$blocks  = $blocks[0]['innerBlocks'];

		// Assert.
		$this->assertSame( array( $parent_category_id, 0, 0, 0, 0, 0 ), array_column( array_column( $blocks[0]['innerBlocks'], 'attrs' ), 'category' ) );
	}

	public function test_tiles_include_default_category(): void {
		// Arrange.
		$default_category_id = self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'name'     => 'Default category',
			)
		);
		update_option( 'default_product_cat', $default_category_id );
		$product_id = self::factory()->post->create(
			array(
				'post_type'   => 'product',
				'post_status' => 'publish',
			)
		);
		wp_set_object_terms( $product_id, array( $default_category_id ), 'product_cat' );

		// Act.
		register_outlet_navigation_6_pattern();
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'outletpro/outlet-navigation-6' );
		$blocks  = parse_blocks( $pattern['content'] );
		$blocks  = $blocks[0]['innerBlocks'];

		// Assert.
		$this->assertContains( $default_category_id, array_column( array_column( $blocks[0]['innerBlocks'], 'attrs' ), 'category' ) );
	}

	public function test_pattern_is_registered_with_expected_properties(): void {
		// Act.
		register_outlet_navigation_6_pattern();
		$pattern = \WP_Block_Patterns_Registry::get_instance()->get_registered( 'outletpro/outlet-navigation-6' );

		// Assert.
		$this->assertSame( 'Outlet navigation with six category tiles', $pattern['title'] );
		$this->assertSame( 'Displays six tiles and a menu.', $pattern['description'] );
		$this->assertSame( array( 'outletpro' ), $pattern['categories'] );
	}

	public function test_content_contains_six_linked_category_tiles(): void {
		// Arrange.
		$shirts_category_id      = self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'name'     => 'Shirts',
			)
		);
		$additional_category_ids = self::factory()->term->create_many(
			5,
			array( 'taxonomy' => 'product_cat' )
		);
		$product_id              = self::factory()->post->create(
			array(
				'post_type'   => 'product',
				'post_status' => 'publish',
			)
		);
		wp_set_object_terms( $product_id, array_merge( array( $shirts_category_id ), $additional_category_ids ), 'product_cat' );

		// Act.
		register_outlet_navigation_6_pattern();
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'outletpro/outlet-navigation-6' );
		$content = $pattern['content'];
		$blocks  = parse_blocks( $content );
		$blocks  = $blocks[0]['innerBlocks'];

		// Assert.
		$this->assertSame( 'outletpro/outlet-menu', $blocks[1]['blockName'] );
		$this->assertSame( 'outletpro/outlet-tile-grid', $blocks[0]['blockName'] );
		$this->assertCount( 6, $blocks[0]['innerBlocks'] );
	}

	public function test_missing_categories_are_padded_to_six_tiles(): void {
		// Act.
		register_outlet_navigation_6_pattern();
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'outletpro/outlet-navigation-6' );
		$blocks  = parse_blocks( $pattern['content'] );
		$blocks  = $blocks[0]['innerBlocks'];

		// Assert.
		$this->assertSame( array( 0, 0, 0, 0, 0, 0 ), array_column( array_column( $blocks[0]['innerBlocks'], 'attrs' ), 'category' ) );
	}

	public function test_selects_only_the_top_six_categories(): void {
		// Arrange.
		$category_ids = self::factory()->term->create_many( 7, array( 'taxonomy' => 'product_cat' ) );
		$product_ids  = self::factory()->post->create_many(
			2,
			array(
				'post_type'   => 'product',
				'post_status' => 'publish',
			)
		);
		wp_set_object_terms( $product_ids[0], $category_ids, 'product_cat' );
		wp_set_object_terms( $product_ids[1], array_slice( $category_ids, 0, 6 ), 'product_cat' );

		// Act.
		register_outlet_navigation_6_pattern();
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'outletpro/outlet-navigation-6' );
		$blocks  = parse_blocks( $pattern['content'] );
		$blocks  = $blocks[0]['innerBlocks'];

		// Assert.
		$this->assertEqualsCanonicalizing( array_slice( $category_ids, 0, 6 ), array_column( array_column( $blocks[0]['innerBlocks'], 'attrs' ), 'category' ) );
	}

	public function test_menu_selects_every_price_and_discount_tier(): void {
		// Arrange.
		truncate_outlet_search_index_taxonomy();
		$price_id    = self::factory()->term->create(
			array(
				'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
				'slug'     => 'outlet-price-25',
			)
		);
		$higher_id   = self::factory()->term->create(
			array(
				'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
				'slug'     => 'outlet-price-100',
			)
		);
		$discount_id = self::factory()->term->create(
			array(
				'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
				'slug'     => 'outlet-discount-50',
			)
		);
		self::factory()->term->create(
			array(
				'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
				'slug'     => 'custom-index',
			)
		);

		// Act.
		register_outlet_navigation_6_pattern();
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'outletpro/outlet-navigation-6' );
		$blocks  = parse_blocks( $pattern['content'] );
		$blocks  = $blocks[0]['innerBlocks'];

		// Assert.
		$this->assertTrue( $blocks[1]['attrs']['showAll'] );
		$this->assertSame( array( $discount_id, $price_id, $higher_id ), array_slice( $blocks[1]['attrs']['items'], -3 ) );
	}

	public function test_menu_selects_all_top_level_categories(): void {
		// Arrange.
		truncate_outlet_search_index_taxonomy();
		$query       = array(
			'taxonomy'   => 'product_cat',
			'parent'     => 0,
			'hide_empty' => false,
			'fields'     => 'ids',
			'orderby'    => 'menu_order',
			'order'      => 'ASC',
		);
		$category_id = self::factory()->term->create( array( 'taxonomy' => 'product_cat' ) );
		self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'parent'   => $category_id,
			)
		);

		// Act.
		register_outlet_navigation_6_pattern();
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'outletpro/outlet-navigation-6' );
		$blocks  = parse_blocks( $pattern['content'] );
		$blocks  = $blocks[0]['innerBlocks'];

		// Assert.
		$this->assertSame( get_terms( $query ), $blocks[1]['attrs']['items'] );
	}
}
