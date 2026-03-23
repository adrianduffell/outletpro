<?php
/**
 * Test the product_publish_page_notice_hook function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_to_clearance;
use function WC_Clearance\create_clearance_page;
use function WC_Clearance\product_publish_page_notice_hook;
use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\seed_clearance_status_taxonomy;
use const WC_Clearance\CLEARANCE_PAGE_OPTION;
use const WC_Clearance\CLEARANCE_STATUS_TAXONOMY;

class Test_Product_Publish_Page_Notice_Hook extends WP_UnitTestCase {

	public function test_renders_notice_when_clearance_products_exist_and_page_is_draft(): void {
		// Arrange.
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		$existing_id = get_option( CLEARANCE_PAGE_OPTION );
		if ( $existing_id > 0 ) {
			wp_delete_post( $existing_id, true );
		}
		delete_option( CLEARANCE_PAGE_OPTION );
		create_clearance_page(); // Creates page as draft.

		// Expect.
		$this->expectOutputRegex( '/wc-clearance-publish-page-notice/' );

		// Act.
		product_publish_page_notice_hook();
	}

	public function test_does_not_render_when_screen_is_not_product_list(): void {
		// Arrange.
		set_current_screen( 'dashboard' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Expect.
		$this->expectOutputString( '' );

		// Act.
		product_publish_page_notice_hook();
	}

	public function test_does_not_render_when_user_cannot_edit_pages(): void {
		// Arrange.
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		// Expect.
		$this->expectOutputString( '' );

		// Act.
		product_publish_page_notice_hook();
	}

	public function test_does_not_render_when_clearance_section_is_empty(): void {
		// Arrange.
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		// No products added to clearance.
		$existing_id = get_option( CLEARANCE_PAGE_OPTION );
		if ( $existing_id > 0 ) {
			wp_delete_post( $existing_id, true );
		}
		delete_option( CLEARANCE_PAGE_OPTION );
		create_clearance_page();

		// Expect.
		$this->expectOutputString( '' );

		// Act.
		product_publish_page_notice_hook();
	}

	public function test_does_not_render_when_clearance_page_is_not_registered(): void {
		// Arrange.
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		$existing_id = get_option( CLEARANCE_PAGE_OPTION );
		if ( $existing_id > 0 ) {
			wp_delete_post( $existing_id, true );
		}
		delete_option( CLEARANCE_PAGE_OPTION ); // No clearance page registered.

		// Expect.
		$this->expectOutputString( '' );

		// Act.
		product_publish_page_notice_hook();
	}

	public function test_does_not_render_when_clearance_page_is_published(): void {
		// Arrange.
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		$existing_id = get_option( CLEARANCE_PAGE_OPTION );
		if ( $existing_id > 0 ) {
			wp_delete_post( $existing_id, true );
		}
		delete_option( CLEARANCE_PAGE_OPTION );
		create_clearance_page();
		$page_id = get_option( CLEARANCE_PAGE_OPTION );
		wp_publish_post( $page_id );

		// Expect.
		$this->expectOutputString( '' );

		// Act.
		product_publish_page_notice_hook();
	}

	public function test_does_not_render_when_taxonomy_throws(): void {
		// Arrange.
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		unregister_taxonomy( CLEARANCE_STATUS_TAXONOMY );

		// Expect.
		$this->expectOutputString( '' );

		// Act.
		product_publish_page_notice_hook();
	}
}
