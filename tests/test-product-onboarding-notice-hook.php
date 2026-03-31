<?php
/**
 * Test the product_onboarding_notice_hook function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_to_clearance;
use function WC_Clearance\create_clearance_page;
use function WC_Clearance\init_admin_product_list_table;
use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\seed_clearance_status_taxonomy;
use const WC_Clearance\ACTIVATED_AT_OPTION;
use const WC_Clearance\CLEARANCE_PAGE_OPTION;
use const WC_Clearance\CLEARANCE_STATUS_TAXONOMY;
use const WC_Clearance\PUBLISH_PAGE_NOTICE_STORAGE_KEY;

class Test_Product_Onboarding_Notice_Hook extends WP_UnitTestCase {

	public function test_renders_notice_when_no_clearance_products(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();

		// Expect.
		$this->expectOutputRegex( '/wc-clearance-onboarding-notice/' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_renders_notice_on_product_edit_screen(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();

		// Expect.
		$this->expectOutputRegex( '/wc-clearance-onboarding-notice/' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_does_not_render_when_screen_is_not_product_list(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'dashboard' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();

		// Expect.
		$this->expectOutputString( '' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_does_not_render_when_user_cannot_edit_products(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();

		// Expect.
		$this->expectOutputString( '' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_does_not_render_when_clearance_products_exist(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );

		// Expect.
		$this->expectOutputString( '' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_does_not_render_when_taxonomy_throws(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		unregister_taxonomy( CLEARANCE_STATUS_TAXONOMY );

		// Expect.
		$this->expectOutputString( '' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_renders_publish_page_notice_when_clearance_products_exist_and_page_is_draft(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		delete_option( CLEARANCE_PAGE_OPTION );
		create_clearance_page(); // Creates page as draft.

		// Expect.
		$this->expectOutputRegex( '/wc-clearance-publish-page-notice/' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_publish_page_notice_contains_dismiss_storage_key_and_is_dismissible(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		delete_option( CLEARANCE_PAGE_OPTION );
		create_clearance_page(); // Creates page as draft.

		// Expect.
		$this->expectOutputRegex( '/is-dismissible/' );
		$this->expectOutputRegex( '/' . preg_quote( PUBLISH_PAGE_NOTICE_STORAGE_KEY, '/' ) . '/' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_does_not_render_publish_page_notice_when_user_cannot_edit_pages(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		// Expect.
		$this->expectOutputString( '' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_renders_onboarding_notice_when_clearance_section_is_empty_and_page_is_draft(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		// No products added to clearance.
		delete_option( CLEARANCE_PAGE_OPTION );
		create_clearance_page();

		// Expect.
		$this->expectOutputRegex( '/wc-clearance-onboarding-notice/' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_does_not_render_publish_page_notice_when_clearance_page_is_not_registered(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		delete_option( CLEARANCE_PAGE_OPTION ); // No clearance page registered.

		// Expect.
		$this->expectOutputString( '' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_does_not_render_publish_page_notice_when_clearance_page_is_published(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		delete_option( CLEARANCE_PAGE_OPTION );
		create_clearance_page();
		$page_id = get_option( CLEARANCE_PAGE_OPTION );
		wp_publish_post( $page_id );

		// Expect.
		$this->expectOutputString( '' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_does_not_render_onboarding_notice_when_activated_more_than_14_days_ago(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		update_option( ACTIVATED_AT_OPTION, time() - ( 15 * DAY_IN_SECONDS ) );

		// Expect.
		$this->expectOutputString( '' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_renders_onboarding_notice_when_activated_within_14_days(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		update_option( ACTIVATED_AT_OPTION, time() - ( 13 * DAY_IN_SECONDS ) );

		// Expect.
		$this->expectOutputRegex( '/wc-clearance-onboarding-notice/' );

		// Act.
		do_action( 'admin_notices' );
	}
}
