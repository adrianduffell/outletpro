<?php
/**
 * Test the product_onboarding_notice_hook function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_to_clearance;
use function WC_Clearance\product_onboarding_notice_hook;
use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\seed_clearance_status_taxonomy;
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
