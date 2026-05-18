<?php
/**
 * Tests for add_product_checkbox_hook().
 *
 * @package WC_Outlet
 */

use function WC_Outlet\add_product_checkbox_hook;
use function WC_Outlet\register_outlet_status_taxonomy;
use function WC_Outlet\seed_outlet_status_taxonomy;

class Test_Add_Product_Checkbox_Hook extends WP_UnitTestCase {

	public function test_settings_link_present_when_settings_screen_enabled(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product         = WC_Helper_Product::create_simple_product();
		$GLOBALS['post'] = get_post( $product->get_id() );
		add_filter( 'wc_outlet_settings_screen_enabled', '__return_true' );

		// Expect.
		$this->expectOutputRegex( '/Edit settings/' );

		// Act.
		add_product_checkbox_hook();

		// Cleanup.
		remove_filter( 'wc_outlet_settings_screen_enabled', '__return_true' );
	}

	public function test_settings_link_absent_when_settings_disabled(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product         = WC_Helper_Product::create_simple_product();
		$GLOBALS['post'] = get_post( $product->get_id() );

		// Expect.
		$this->expectOutputRegex( '/^(?!.*Edit Settings).*/s' ); // Doesn't contain "Edit Settings".

		// Act.
		add_product_checkbox_hook();
	}
}
