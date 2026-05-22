<?php
/**
 * Test the product_onboarding_notice_hook function.
 *
 * @package WC_Outlet
 */

use function WC_Outlet\add_to_outlet;
use function WC_Outlet\create_outlet_page;
use function WC_Outlet\init_admin_product_list_table;
use function WC_Outlet\register_outlet_status_taxonomy;
use function WC_Outlet\seed_outlet_status_taxonomy;
use const WC_Outlet\ACTIVATED_AT_OPTION;
use const WC_Outlet\ONBOARDING_DISMISS_STORAGE_KEY;
use const WC_Outlet\OUTLET_PAGE_OPTION;
use const WC_Outlet\OUTLET_STATUS_TAXONOMY;

class Test_Product_Onboarding_Notice_Hook extends WP_UnitTestCase {

	public function test_renders_notice_when_no_outlet_products(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();

		// Expect.
		$this->expectOutputRegex( '/wc-outlet-onboarding-notice/' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_renders_notice_on_product_edit_screen(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();

		// Expect.
		$this->expectOutputRegex( '/wc-outlet-onboarding-notice/' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_does_not_render_when_screen_is_not_product_list(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'dashboard' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();

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
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();

		// Expect.
		$this->expectOutputString( '' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_does_not_render_when_outlet_products_exist(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );

		// Expect.
		$this->expectOutputRegex( '/wc-outlet-onboarding-notice/' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_does_not_render_when_taxonomy_throws(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		unregister_taxonomy( OUTLET_STATUS_TAXONOMY );

		// Expect.
		$this->expectOutputString( '' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_renders_publish_page_notice_when_outlet_products_exist_and_page_is_draft(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		delete_option( OUTLET_PAGE_OPTION );
		create_outlet_page(); // Creates page as draft.

		// Expect.
		$this->expectOutputRegex( '/wc-outlet-onboarding-notice/' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_publish_page_notice_contains_dismiss_storage_key_and_is_dismissible(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		delete_option( OUTLET_PAGE_OPTION );
		create_outlet_page(); // Creates page as draft.

		// Expect.
		$this->expectOutputRegex( '/is-dismissible/' );
		$this->expectOutputRegex( '/' . preg_quote( ONBOARDING_DISMISS_STORAGE_KEY, '/' ) . '/' );

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

	public function test_renders_onboarding_notice_when_outlet_section_is_empty_and_page_is_draft(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		// No products added to outlet.
		delete_option( OUTLET_PAGE_OPTION );
		create_outlet_page();

		// Expect.
		$this->expectOutputRegex( '/wc-outlet-onboarding-notice/' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_does_not_render_publish_page_notice_when_outlet_page_is_not_registered(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		delete_option( OUTLET_PAGE_OPTION ); // No outlet page registered.

		// Expect.
		$this->expectOutputRegex( '/wc-outlet-onboarding-notice/' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_does_not_render_publish_page_notice_when_outlet_page_is_published(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		delete_option( OUTLET_PAGE_OPTION );
		create_outlet_page();
		$page_id = get_option( OUTLET_PAGE_OPTION );
		wp_publish_post( $page_id );

		// Expect.
		$this->expectOutputRegex( '/wc-outlet-onboarding-notice/' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_publish_page_notice_contains_edit_page_link(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		delete_option( OUTLET_PAGE_OPTION );
		create_outlet_page(); // Creates page as draft.
		// Act.
		ob_start();
		do_action( 'admin_notices' );
		$output = (string) ob_get_clean();

		// Assert.
		$this->assertMatchesRegularExpression(
			'/<a[^>]*>Edit page<\/a>/',
			$output
		);
	}

	public function test_products_added_notice_contains_dismiss_storage_key_and_is_dismissible(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		delete_option( OUTLET_PAGE_OPTION ); // No outlet page registered.

		// Expect.
		$this->expectOutputRegex( '/is-dismissible/' );
		$this->expectOutputRegex( '/' . preg_quote( ONBOARDING_DISMISS_STORAGE_KEY, '/' ) . '/' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_does_not_render_onboarding_notice_when_activated_more_than_14_days_ago(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
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
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		update_option( ACTIVATED_AT_OPTION, time() - ( 13 * DAY_IN_SECONDS ) );

		// Expect.
		$this->expectOutputRegex( '/wc-outlet-onboarding-notice/' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_empty_state_notice_contains_outlet_section_is_empty_message(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();

		// Expect.
		$this->expectOutputRegex( '/The store’s outlet is empty\./' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_draft_page_notice_contains_product_count(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		delete_option( OUTLET_PAGE_OPTION );
		create_outlet_page(); // Creates page as draft.

		// Expect.
		$this->expectOutputRegex( '/The store’s outlet has 1 product\./' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_draft_page_notice_contains_plural_product_count(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product1 = \WC_Helper_Product::create_simple_product();
		$product2 = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product1 );
		add_to_outlet( $product2 );
		delete_option( OUTLET_PAGE_OPTION );
		create_outlet_page(); // Creates page as draft.

		// Expect.
		$this->expectOutputRegex( '/The store’s outlet has 2 products\./' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_no_page_notice_contains_tip_message_when_page_not_registered(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		delete_option( OUTLET_PAGE_OPTION ); // No outlet page registered.

		// Expect.
		$this->expectOutputRegex( '/Tip: add it to a page or post using the outlet block\./' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_ready_state_notice_contains_ready_message_when_page_is_published(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		delete_option( OUTLET_PAGE_OPTION );
		create_outlet_page();
		$page_id = get_option( OUTLET_PAGE_OPTION );
		wp_publish_post( $page_id );

		// Expect.
		$this->expectOutputRegex( '/The store’s outlet is ready\./' );

		// Act.
		do_action( 'admin_notices' );
	}

	public function test_ready_state_notice_contains_view_page_link_when_page_is_published(): void {
		// Arrange.
		init_admin_product_list_table();
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		delete_option( OUTLET_PAGE_OPTION );
		create_outlet_page();
		$page_id = get_option( OUTLET_PAGE_OPTION );
		wp_publish_post( $page_id );

		// Expect.
		$expected_url = esc_url( get_permalink( $page_id ) );
		$this->expectOutputRegex( '/href="' . preg_quote( $expected_url, '/' ) . '">View page<\/a>/' );

		// Act.
		do_action( 'admin_notices' );
	}
}
