<?php
/**
 * Tests for filter_outlet_page_request_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\init_taxonomies;
use const OutletPro\OUTLET_PAGE_OPTION;
use const OutletPro\OUTLET_STATUS_CANONICAL_TERM;
use const OutletPro\OUTLET_STATUS_TAXONOMY;

class Test_Filter_Outlet_Page_Request_Hook extends WP_UnitTestCase {

	public function test_paginated_outlet_page_url(): void {
		// Arrange.
		init_taxonomies();
		$page_id = self::factory()->post->create(
			array(
				'post_type' => 'page',
				'post_name' => 'clearance',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );
		update_option( 'posts_per_page', 1 );
		update_option( 'woocommerce_catalog_rows', 1 );
		update_option( 'woocommerce_catalog_columns', 1 );
		$first  = WC_Helper_Product::create_simple_product();
		$second = WC_Helper_Product::create_simple_product();
		add_to_outlet( $first );
		add_to_outlet( $second );
		$this->set_permalink_structure( '/%postname%/' );

		// Act.
		$this->go_to( trailingslashit( get_permalink( $page_id ) ) . 'page/2/' );

		// Assert.
		$this->assertTrue( is_tax( OUTLET_STATUS_TAXONOMY, OUTLET_STATUS_CANONICAL_TERM ) );
		$this->assertSame( get_permalink( $page_id ), get_term_link( get_queried_object() ) );
		$this->assertSame( trailingslashit( get_permalink( $page_id ) ) . 'page/2/', get_pagenum_link( 2, false ) );
		$this->assertSame( 2, get_query_var( 'paged' ) );
		$this->assertSame( 2, $GLOBALS['wp_query']->found_posts );
	}

	public function test_legacy_outlet_template_keeps_its_page_query(): void {
		// Arrange.
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		update_post_meta( $page_id, '_wp_page_template', 'outlet-page' );
		$this->set_permalink_structure( '' );

		// Act.
		$this->go_to( get_permalink( $page_id ) );

		// Assert.
		$this->assertTrue( is_page( $page_id ) );
		$this->assertEmpty( get_query_var( 'outletpro' ) );
	}

	public function test_removing_legacy_template_enables_catalog_routing(): void {
		// Arrange.
		init_taxonomies();
		$page_id = self::factory()->post->create(
			array(
				'post_type' => 'page',
				'post_name' => 'outlet',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );
		update_post_meta( $page_id, '_wp_page_template', 'outlet-page' );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );

		// Act.
		$this->go_to( get_permalink( $page_id ) );
		$was_page = is_page( $page_id );
		update_post_meta( $page_id, '_wp_page_template', 'default' );
		$this->go_to( get_permalink( $page_id ) );

		// Assert.
		$this->assertTrue( $was_page );
		$this->assertTrue( is_tax( OUTLET_STATUS_TAXONOMY, OUTLET_STATUS_CANONICAL_TERM ) );
		$this->assertTrue( get_query_var( 'outletpro' ) );
		$this->assertSame( array( $product->get_id() ), wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' ) );
	}

	public function test_draft_outlet_does_not_expose_products_to_visitors(): void {
		// Arrange.
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'draft',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );
		wp_set_current_user( 0 );

		// Act.
		$this->go_to( add_query_arg( 'page_id', $page_id, home_url( '/' ) ) );

		// Assert.
		$this->assertFalse( is_product_taxonomy() );
		$this->assertEmpty( get_query_var( 'outletpro' ) );
	}

	public function test_authorized_draft_preview_uses_the_outlet_archive(): void {
		// Arrange.
		init_taxonomies();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'draft',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );

		// Act.
		$this->go_to( add_query_arg( 'page_id', $page_id, home_url( '/' ) ) );

		// Assert.
		$this->assertTrue( get_query_var( 'outletpro' ) );
		$this->assertSame( array( $product->get_id() ), wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' ) );
	}

	public function test_password_protected_outlet_keeps_wordpress_password_protection(): void {
		// Arrange.
		$page_id = self::factory()->post->create(
			array(
				'post_type'     => 'page',
				'post_password' => 'secret',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );

		// Act.
		$this->go_to( get_permalink( $page_id ) );

		// Assert.
		$this->assertTrue( is_page( $page_id ) );
		$this->assertEmpty( get_query_var( 'outletpro' ) );
	}

	public function test_ordinary_page_keeps_its_page_query(): void {
		// Arrange.
		$outlet_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$page_id   = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $outlet_id );

		// Act.
		$this->go_to( get_permalink( $page_id ) );

		// Assert.
		$this->assertTrue( is_page( $page_id ) );
		$this->assertEmpty( get_query_var( 'outletpro' ) );
	}

	public function test_missing_outlet_setting_preserves_the_current_page_request(): void {
		// Arrange.
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		delete_option( OUTLET_PAGE_OPTION );

		// Act.
		$this->go_to( get_permalink( $page_id ) );

		// Assert.
		$this->assertTrue( is_page( $page_id ) );
		$this->assertEmpty( get_query_var( 'outletpro' ) );
	}

	public function test_deleted_outlet_reference_preserves_other_page_requests(): void {
		// Arrange.
		$deleted_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$page_id    = self::factory()->post->create( array( 'post_type' => 'page' ) );
		wp_delete_post( $deleted_id, true );
		update_option( OUTLET_PAGE_OPTION, $deleted_id );

		// Act.
		$this->go_to( get_permalink( $page_id ) );

		// Assert.
		$this->assertTrue( is_page( $page_id ) );
		$this->assertEmpty( get_query_var( 'outletpro' ) );
	}

	public function test_non_page_outlet_reference_keeps_its_normal_post_request(): void {
		// Arrange.
		$post_id = self::factory()->post->create();
		update_option( OUTLET_PAGE_OPTION, $post_id );

		// Act.
		$this->go_to( get_permalink( $post_id ) );

		// Assert.
		$this->assertTrue( is_single( $post_id ) );
		$this->assertEmpty( get_query_var( 'outletpro' ) );
	}

	public function test_trashed_outlet_page_does_not_become_an_outlet_archive(): void {
		// Arrange.
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		wp_trash_post( $page_id );
		update_option( OUTLET_PAGE_OPTION, $page_id );

		// Act.
		$this->go_to( add_query_arg( 'page_id', $page_id, home_url( '/' ) ) );

		// Assert.
		$this->assertFalse( is_product_taxonomy() );
		$this->assertEmpty( get_query_var( 'outletpro' ) );
	}
}
