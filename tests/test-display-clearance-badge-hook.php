<?php
/**
 * Tests for display_clearance_badge_hook().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_to_clearance;
use function WC_Clearance\init_woocommerce_template_hooks;
use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\seed_clearance_status_taxonomy;

class Test_Display_Clearance_Badge_Hook extends WP_UnitTestCase {

	public function test_hook_is_registered_after_init_woocommerce_template_hooks(): void {
		// Arrange.
		remove_action( 'woocommerce_single_product_summary', 'WC_Clearance\display_clearance_badge_hook', 15 );

		// Act.
		init_woocommerce_template_hooks();

		// Assert.
		$this->assertSame( 15, has_action( 'woocommerce_single_product_summary', 'WC_Clearance\display_clearance_badge_hook' ) );
	}

	public function test_outputs_badge_html_for_clearance_product(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		$GLOBALS['post'] = get_post( $product->get_id() );
		init_woocommerce_template_hooks();

		// Expect.
		$this->expectOutputRegex( '/wc-clearance-badge/' );

		// Act.
		do_action( 'woocommerce_single_product_summary' );
	}

	public function test_outputs_clearance_message_for_clearance_product(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		$GLOBALS['post'] = get_post( $product->get_id() );
		init_woocommerce_template_hooks();

		// Expect.
		$this->expectOutputRegex( '/Choose carefully! Clearance products are ineligible for returns\./' );

		// Act.
		do_action( 'woocommerce_single_product_summary' );
	}

	public function test_outputs_nothing_for_non_clearance_product(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product         = WC_Helper_Product::create_simple_product();
		$GLOBALS['post'] = get_post( $product->get_id() );
		init_woocommerce_template_hooks();

		// Expect.
		$this->expectOutputRegex( '/^(?!.*wc-clearance-badge).*/s' ); // Does not contain the clearance badge.

		// Act.
		do_action( 'woocommerce_single_product_summary' );
	}
}
