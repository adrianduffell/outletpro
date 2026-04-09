<?php
/**
 * Tests for add_product_checkbox_hook() and settings_enabled().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_product_checkbox_hook;
use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\seed_clearance_status_taxonomy;
use function WC_Clearance\settings_enabled;

class Test_Add_Product_Checkbox_Hook extends WP_UnitTestCase {

	public function test_settings_enabled_returns_false_by_default(): void {
		// Act.
		$result = settings_enabled();

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_settings_enabled_returns_true_when_filter_enables_it(): void {
		// Arrange.
		add_filter( 'wc_clearance_settings_enabled', '__return_true' );

		// Act.
		$result = settings_enabled();

		// Assert.
		$this->assertTrue( $result );

		// Cleanup.
		remove_filter( 'wc_clearance_settings_enabled', '__return_true' );
	}

	public function test_settings_link_present_when_settings_enabled(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product         = WC_Helper_Product::create_simple_product();
		$GLOBALS['post'] = get_post( $product->get_id() );
		add_filter( 'wc_clearance_settings_enabled', '__return_true' );

		// Act.
		add_product_checkbox_hook();

		// Assert.
		$this->expectOutputRegex( '/Edit settings/' );

		// Cleanup.
		remove_filter( 'wc_clearance_settings_enabled', '__return_true' );
	}

	public function test_settings_link_absent_when_settings_disabled(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product         = WC_Helper_Product::create_simple_product();
		$GLOBALS['post'] = get_post( $product->get_id() );

		// Act.
		add_product_checkbox_hook();

		// Assert: help text present but settings link absent.
		$this->expectOutputRegex( '/clearance section and display a badge\.<\/div>/' );
	}
}
