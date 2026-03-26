<?php
/**
 * Test the product_clearance_complete_notice_hook function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_to_clearance;
use function WC_Clearance\create_clearance_page;
use function WC_Clearance\product_clearance_complete_notice_hook;
use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\seed_clearance_status_taxonomy;
use const WC_Clearance\ONBOARDING_DISMISS_KEY;
use const WC_Clearance\CLEARANCE_PAGE_OPTION;
use const WC_Clearance\CLEARANCE_STATUS_TAXONOMY;

class Test_Product_Clearance_Complete_Notice_Hook extends WP_UnitTestCase {

	public function test_renders_notice_when_products_exist_and_page_is_published(): void {
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
		$this->expectOutputRegex( '/wc-clearance-complete-notice/' );

		// Act.
		product_clearance_complete_notice_hook();
	}

	public function test_notice_contains_checklist_with_both_items_checked(): void {
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

		// Act.
		ob_start();
		product_clearance_complete_notice_hook();
		$output = ob_get_clean();

		// Assert: both checklist items are checked.
		$checked_count = substr_count( $output, 'wc-clearance-checklist-item--checked' );
		$this->assertSame( 2, $checked_count );
	}

	public function test_notice_contains_dismiss_storage_key_and_is_dismissible(): void {
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
		$this->expectOutputRegex( '/is-dismissible/' );
		$this->expectOutputRegex( '/' . preg_quote( ONBOARDING_DISMISS_KEY, '/' ) . '/' );

		// Act.
		product_clearance_complete_notice_hook();
	}

	public function test_notice_contains_manage_menus_link_when_theme_supports_menus(): void {
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
		add_theme_support( 'menus' );

		// Act.
		ob_start();
		product_clearance_complete_notice_hook();
		$output = ob_get_clean();
		remove_theme_support( 'menus' );

		// Assert.
		$this->assertStringContainsString( 'nav-menus.php', $output );
		$this->assertStringContainsString( 'Manage menus', $output );
	}

	public function test_notice_omits_manage_menus_link_when_theme_does_not_support_menus(): void {
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
		remove_theme_support( 'menus' );

		// Act.
		ob_start();
		product_clearance_complete_notice_hook();
		$output = ob_get_clean();

		// Assert.
		$this->assertStringNotContainsString( 'nav-menus.php', $output );
		$this->assertStringNotContainsString( 'Manage menus', $output );
	}

	public function test_does_not_render_when_screen_is_not_product_list(): void {
		// Arrange.
		set_current_screen( 'dashboard' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Expect.
		$this->expectOutputString( '' );

		// Act.
		product_clearance_complete_notice_hook();
	}

	public function test_does_not_render_when_user_cannot_edit_products(): void {
		// Arrange.
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		// Expect.
		$this->expectOutputString( '' );

		// Act.
		product_clearance_complete_notice_hook();
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
		$page_id = get_option( CLEARANCE_PAGE_OPTION );
		wp_publish_post( $page_id );

		// Expect.
		$this->expectOutputString( '' );

		// Act.
		product_clearance_complete_notice_hook();
	}

	public function test_does_not_render_when_clearance_page_is_not_published(): void {
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
		$this->expectOutputString( '' );

		// Act.
		product_clearance_complete_notice_hook();
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
		product_clearance_complete_notice_hook();
	}
}
