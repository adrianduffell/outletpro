<?php
/**
 * Test the publish-page state of product_onboarding_notice_hook.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_to_clearance;
use function WC_Clearance\create_clearance_page;
use function WC_Clearance\product_onboarding_notice_hook;
use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\seed_clearance_status_taxonomy;
use const WC_Clearance\CLEARANCE_PAGE_OPTION;
use const WC_Clearance\ONBOARDING_DISMISS_KEY;

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
		product_onboarding_notice_hook();
	}

	public function test_notice_contains_checklist_with_products_checked_and_page_unchecked(): void {
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

		// Act.
		ob_start();
		product_onboarding_notice_hook();
		$output = ob_get_clean();

		// Assert: checklist is present, only the "Include products" item is checked.
		$this->assertStringContainsString( 'wc-clearance-checklist', $output );
		$checked_count = substr_count( $output, 'wc-clearance-checklist-item--checked' );
		$this->assertSame( 1, $checked_count );
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
		create_clearance_page(); // Creates page as draft.

		// Expect.
		$this->expectOutputRegex( '/is-dismissible/' );
		$this->expectOutputRegex( '/' . preg_quote( ONBOARDING_DISMISS_KEY, '/' ) . '/' );

		// Act.
		product_onboarding_notice_hook();
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
		product_onboarding_notice_hook();
	}

}
