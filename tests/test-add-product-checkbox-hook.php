<?php
/**
 * Tests for add_product_checkbox_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\init_admin_product_options;
use function OutletPro\init_taxonomies;
use function OutletPro\seed_outlet_status_taxonomy;
use const OutletPro\OUTLET_PAGE_OPTION;

class Test_Add_Product_Checkbox_Hook extends WP_UnitTestCase {

	public function test_edit_page_link_shown_for_draft_page(): void {
		// Arrange.
		init_admin_product_options();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'draft',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$product         = WC_Helper_Product::create_simple_product();
		$GLOBALS['post'] = get_post( $product->get_id() );
		$page_url        = preg_quote( esc_attr( admin_url( 'post.php?post=' . $page_id . '&action=edit' ) ), '/' );

		// Expect.
		$this->expectOutputRegex( '/<a href="' . $page_url . '" class="outletpro-button-link">Edit page<\/a>/' );

		// Act.
		do_action( 'woocommerce_product_options_inventory_product_data' );
	}

	public function test_edit_page_link_shown_for_published_page(): void {
		// Arrange.
		init_admin_product_options();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$product         = WC_Helper_Product::create_simple_product();
		$GLOBALS['post'] = get_post( $product->get_id() );
		$page_url        = preg_quote( esc_attr( admin_url( 'post.php?post=' . $page_id . '&action=edit' ) ), '/' );

		// Expect.
		$this->expectOutputRegex( '/<a href="' . $page_url . '" class="outletpro-button-link">Edit page<\/a>/' );

		// Act.
		do_action( 'woocommerce_product_options_inventory_product_data' );
	}

	public function test_checkbox_help_shown_without_link_when_no_outlet_page_configured(): void {
		// Arrange.
		init_admin_product_options();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		delete_option( OUTLET_PAGE_OPTION );
		$product         = WC_Helper_Product::create_simple_product();
		$GLOBALS['post'] = get_post( $product->get_id() );

		// Expect.
		$this->expectOutputRegex( '/^(?!.*Edit page).*Sell remaining stock on the outlet page\./s' );

		// Act.
		do_action( 'woocommerce_product_options_inventory_product_data' );
	}

	public function test_checkbox_help_shown_without_link_when_outlet_page_deleted(): void {
		// Arrange.
		init_admin_product_options();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		wp_delete_post( $page_id, true );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$product         = WC_Helper_Product::create_simple_product();
		$GLOBALS['post'] = get_post( $product->get_id() );

		// Expect.
		$this->expectOutputRegex( '/^(?!.*Edit page).*Sell remaining stock on the outlet page\./s' );

		// Act.
		do_action( 'woocommerce_product_options_inventory_product_data' );
	}

	public function test_checkbox_help_shown_without_link_when_user_cannot_edit_page(): void {
		// Arrange.
		init_admin_product_options();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( OUTLET_PAGE_OPTION, $page_id );
		$product         = WC_Helper_Product::create_simple_product();
		$GLOBALS['post'] = get_post( $product->get_id() );

		// Expect.
		$this->expectOutputRegex( '/^(?!.*Edit page).*Sell remaining stock on the outlet page\./s' );

		// Act.
		do_action( 'woocommerce_product_options_inventory_product_data' );
	}

	public function test_checkbox_help_shown_without_link_when_outlet_page_option_invalid(): void {
		// Arrange.
		init_admin_product_options();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		update_option( OUTLET_PAGE_OPTION, 'not-an-int' );
		$product         = WC_Helper_Product::create_simple_product();
		$GLOBALS['post'] = get_post( $product->get_id() );

		// Expect.
		$this->expectOutputRegex( '/^(?!.*Edit page).*Sell remaining stock on the outlet page\./s' );

		// Act.
		do_action( 'woocommerce_product_options_inventory_product_data' );
	}
}
