<?php
/**
 * Test the product_onboarding_notice_hook function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_to_clearance;
use function WC_Clearance\create_clearance_page;
use function WC_Clearance\product_onboarding_notice_hook;
use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\seed_clearance_status_taxonomy;
use const WC_Clearance\CLEARANCE_PAGE_OPTION;
use const WC_Clearance\CLEARANCE_STATUS_TAXONOMY;

class Test_Product_Onboarding_Notice_Hook extends WP_UnitTestCase {

	public function test_renders_notice_when_no_clearance_products(): void {
		// Arrange.
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();

		// Act.
		ob_start();
		product_onboarding_notice_hook();
		$output = ob_get_clean();

		// Assert.
		$this->assertStringContainsString( 'wc-clearance-onboarding-notice', $output );
		$this->assertStringContainsString( 'wc-clearance-checklist', $output );
	}

	public function test_checklist_shows_both_items_unchecked_when_page_not_published(): void {
		// Arrange.
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
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

		// Assert: both items are unchecked.
		$this->assertStringNotContainsString( 'wc-clearance-checklist-item--checked', $output );
	}

	public function test_checklist_shows_page_checked_when_page_is_published(): void {
		// Arrange.
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
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
		product_onboarding_notice_hook();
		$output = ob_get_clean();

		// Assert: "Publish the page" item is checked.
		$this->assertStringContainsString( 'wc-clearance-checklist-item--checked', $output );
	}

	public function test_does_not_render_when_screen_is_not_product_list(): void {
		// Arrange.
		set_current_screen( 'dashboard' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();

		// Act.
		ob_start();
		product_onboarding_notice_hook();
		$output = ob_get_clean();

		// Assert.
		$this->assertEmpty( $output );
	}

	public function test_does_not_render_when_user_cannot_edit_products(): void {
		// Arrange.
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();

		// Act.
		ob_start();
		product_onboarding_notice_hook();
		$output = ob_get_clean();

		// Assert.
		$this->assertEmpty( $output );
	}

	public function test_does_not_render_when_clearance_products_exist(): void {
		// Arrange.
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );

		// Act.
		ob_start();
		product_onboarding_notice_hook();
		$output = ob_get_clean();

		// Assert.
		$this->assertEmpty( $output );
	}

	public function test_does_not_render_when_taxonomy_throws(): void {
		// Arrange.
		set_current_screen( 'edit-product' );
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		unregister_taxonomy( CLEARANCE_STATUS_TAXONOMY );

		// Act.
		ob_start();
		product_onboarding_notice_hook();
		$output = ob_get_clean();

		// Assert.
		$this->assertEmpty( $output );
	}
}
