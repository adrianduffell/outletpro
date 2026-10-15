<?php
/**
 * Tests for the filtered outlet permalink helper.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_outlet_permalink_with_filter;
use function OutletPro\init_taxonomies;
use const OutletPro\OUTLET_PAGE_OPTION;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Get_Outlet_Permalink_With_Filter extends WP_UnitTestCase {

	public function test_returns_price_tier_permalink(): void {
		// Arrange.
		init_taxonomies();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$term = self::factory()->term->create_and_get(
			array(
				'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
				'slug'     => 'outlet-price-25',
			)
		);

		// Act.
		$permalink = get_outlet_permalink_with_filter( $term );

		// Assert.
		$this->assertSame( add_query_arg( 'outlet_price', 25, get_permalink( $page_id ) ), $permalink );
	}

	public function test_returns_discount_tier_permalink(): void {
		// Arrange.
		init_taxonomies();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$term = self::factory()->term->create_and_get(
			array(
				'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
				'slug'     => 'outlet-discount-50',
			)
		);

		// Act.
		$permalink = get_outlet_permalink_with_filter( $term );

		// Assert.
		$this->assertSame( add_query_arg( 'outlet_discount', 50, get_permalink( $page_id ) ), $permalink );
	}

	public function test_returns_category_permalink(): void {
		// Arrange.
		init_taxonomies();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$term = self::factory()->term->create_and_get(
			array(
				'taxonomy' => 'product_cat',
				'slug'     => 'clothing',
			)
		);

		// Act.
		$permalink = get_outlet_permalink_with_filter( $term );

		// Assert.
		$this->assertSame( add_query_arg( 'outlet_category', 'clothing', get_permalink( $page_id ) ), $permalink );
	}

	public function test_returns_null_without_outlet_page(): void {
		// Arrange.
		init_taxonomies();
		delete_option( OUTLET_PAGE_OPTION );
		$term = self::factory()->term->create_and_get(
			array(
				'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
				'slug'     => 'outlet-price-25',
			)
		);

		// Act.
		$permalink = get_outlet_permalink_with_filter( $term );

		// Assert.
		$this->assertNull( $permalink );
	}
}
