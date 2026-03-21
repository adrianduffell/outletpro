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

class Test_Onboarding_Notice extends WP_UnitTestCase {

	public function test_returns_true_when_no_clearance_products(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();

		// Act.
		$result = should_show_onboarding_notice();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_returns_false_when_clearance_products_exist(): void {
		// Arrange.
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
		unregister_taxonomy( \WC_Clearance\CLEARANCE_STATUS_TAXONOMY );

		// Act.
		$result = should_show_onboarding_notice();

		// Assert.
		$this->assertFalse( $result );
	}
}
