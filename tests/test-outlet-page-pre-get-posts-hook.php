<?php
/**
 * Tests for outlet_page_pre_get_posts_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\init_taxonomies;
use function OutletPro\seed_outlet_status_taxonomy;
use const OutletPro\OUTLET_PAGE_OPTION;
use const OutletPro\OUTLET_STATUS_CANONICAL_TERM;
use const OutletPro\OUTLET_STATUS_TAXONOMY;

class Test_Outlet_Page_Pre_Get_Posts_Hook extends WP_UnitTestCase {

	public function test_category_navigation_intersects_with_outlet_products(): void {
		// Arrange.
		init_taxonomies();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$category = self::factory()->term->create(
			array(
				'taxonomy' => 'product_cat',
				'slug'     => 'boots',
			)
		);
		$match    = WC_Helper_Product::create_simple_product();
		$other    = WC_Helper_Product::create_simple_product();
		$regular  = WC_Helper_Product::create_simple_product();
		add_to_outlet( $match );
		add_to_outlet( $other );
		wp_set_object_terms( $match->get_id(), $category, 'product_cat' );
		wp_set_object_terms( $regular->get_id(), $category, 'product_cat' );

		// Act.
		$this->go_to( add_query_arg( 'outlet_category', 'boots', get_permalink( $page_id ) ) );

		// Assert.
		$this->assertSame( array( $match->get_id() ), wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' ) );
	}

	public function test_shop_archive_keeps_regular_products(): void {
		// Arrange.
		init_taxonomies();
		$outlet_page = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$shop        = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $outlet_page );
		update_option( 'woocommerce_shop_page_id', $shop );
		$product = WC_Helper_Product::create_simple_product();

		// Act.
		$this->go_to( add_query_arg( 'post_type', 'product', home_url( '/' ) ) );

		// Assert.
		$this->assertContains( $product->get_id(), wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' ) );
		$this->assertEmpty( get_query_var( 'outletpro' ) );
	}

	public function test_category_archive_keeps_regular_products(): void {
		// Arrange.
		init_taxonomies();
		$outlet_page = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $outlet_page );
		$product  = WC_Helper_Product::create_simple_product();
		$category = self::factory()->term->create( array( 'taxonomy' => 'product_cat' ) );
		wp_set_object_terms( $product->get_id(), $category, 'product_cat' );

		// Act.
		$this->go_to( get_term_link( $category, 'product_cat' ) );

		// Assert.
		$this->assertContains( $product->get_id(), wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' ) );
		$this->assertEmpty( get_query_var( 'outletpro' ) );
	}

	public function test_outlet_remains_a_term_archive_with_a_product_post_type_parameter(): void {
		// Arrange.
		init_taxonomies();
		seed_outlet_status_taxonomy();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );

		// Act.
		$this->go_to( add_query_arg( 'post_type', 'product', get_permalink( $page_id ) ) );

		// Assert.
		$this->assertInstanceOf( WP_Term::class, get_queried_object() );
		$this->assertSame( get_queried_object()->term_id, get_queried_object_id() );
		$this->assertTrue( is_tax( OUTLET_STATUS_TAXONOMY, OUTLET_STATUS_CANONICAL_TERM ) );
		$this->assertTrue( is_product_taxonomy() );
		$this->assertFalse( is_post_type_archive() );
		$this->assertFalse( is_shop() );
	}
}
