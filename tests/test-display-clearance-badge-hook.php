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
use const WC_Clearance\CLEARANCE_BADGE_BG_COLOUR_DEFAULT;
use const WC_Clearance\CLEARANCE_BADGE_TEXT_COLOUR_DEFAULT;

class Test_Display_Clearance_Badge_Hook extends WP_UnitTestCase {

	public function test_hook_is_registered_after_init_woocommerce_template_hooks(): void {
		// Arrange.
		remove_action( 'woocommerce_single_product_summary', 'WC_Clearance\display_clearance_badge_hook', 15 );

		// Act.
		init_woocommerce_template_hooks();

		// Assert.
		$this->assertSame( 15, has_action( 'woocommerce_single_product_summary', 'WC_Clearance\display_clearance_badge_hook' ) );
	}

	public function test_custom_hook_filter_registers_badge_on_custom_action(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		$callback = static function () {
			return 'custom_product_hook';
		};
		add_filter( 'wc_clearance_badge_single_product_hook', $callback );

		// Act.
		init_woocommerce_template_hooks();

		// Assert.
		$this->assertSame( 15, has_action( 'custom_product_hook', 'WC_Clearance\display_clearance_badge_hook' ) );
		$this->assertFalse( has_action( 'woocommerce_single_product_summary', 'WC_Clearance\display_clearance_badge_hook' ) );

		// Cleanup.
		remove_filter( 'wc_clearance_badge_single_product_hook', $callback );
		remove_action( 'custom_product_hook', 'WC_Clearance\display_clearance_badge_hook', 15 );
	}

	public function test_custom_priority_filter_registers_badge_at_custom_priority(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		$callback = static function () {
			return 6;
		};
		add_filter( 'wc_clearance_badge_single_product_priority', $callback );

		// Act.
		init_woocommerce_template_hooks();

		// Assert.
		$this->assertSame( 6, has_action( 'woocommerce_single_product_summary', 'WC_Clearance\display_clearance_badge_hook' ) );

		// Cleanup.
		remove_filter( 'wc_clearance_badge_single_product_priority', $callback );
		remove_action( 'woocommerce_single_product_summary', 'WC_Clearance\display_clearance_badge_hook', 6 );
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

	public function test_outputs_default_bg_colour_when_no_theme_mod_is_stored(): void {
		// Arrange.
		switch_theme( 'storefront' );
		delete_option( 'theme_mods_' . get_option( 'stylesheet' ) );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		$GLOBALS['post'] = get_post( $product->get_id() );
		init_woocommerce_template_hooks();

		// Expect.
		$this->expectOutputRegex( '/background-color:' . preg_quote( CLEARANCE_BADGE_BG_COLOUR_DEFAULT, '/' ) . '/' );

		// Act.
		do_action( 'woocommerce_single_product_summary' );
	}

	public function test_outputs_default_text_colour_when_no_theme_mod_is_stored(): void {
		// Arrange.
		switch_theme( 'storefront' );
		delete_option( 'theme_mods_' . get_option( 'stylesheet' ) );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		$GLOBALS['post'] = get_post( $product->get_id() );
		init_woocommerce_template_hooks();

		// Expect.
		$this->expectOutputRegex( '/color:' . preg_quote( CLEARANCE_BADGE_TEXT_COLOUR_DEFAULT, '/' ) . '/' );

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
