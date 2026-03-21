<?php
/**
 * Test the should_show_onboarding_notice function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_to_clearance;
use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\seed_clearance_status_taxonomy;
use function WC_Clearance\should_show_onboarding_notice;
use const WC_Clearance\ONBOARDING_NOTICE_DISMISSED_META;

class Test_Onboarding_Notice extends WP_UnitTestCase {

	public function test_returns_true_when_not_dismissed_and_no_clearance_products(): void {
		// Arrange.
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		delete_user_meta( $user_id, ONBOARDING_NOTICE_DISMISSED_META );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();

		// Act.
		$result = should_show_onboarding_notice();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_returns_false_when_dismissed(): void {
		// Arrange.
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		update_user_meta( $user_id, ONBOARDING_NOTICE_DISMISSED_META, '1' );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();

		// Act.
		$result = should_show_onboarding_notice();

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_returns_false_when_clearance_products_exist(): void {
		// Arrange.
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		delete_user_meta( $user_id, ONBOARDING_NOTICE_DISMISSED_META );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );

		// Act.
		$result = should_show_onboarding_notice();

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_returns_false_when_taxonomy_not_registered(): void {
		// Arrange.
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		delete_user_meta( $user_id, ONBOARDING_NOTICE_DISMISSED_META );
		unregister_taxonomy( \WC_Clearance\CLEARANCE_STATUS_TAXONOMY );

		// Act.
		$result = should_show_onboarding_notice();

		// Assert.
		$this->assertFalse( $result );
	}
}
