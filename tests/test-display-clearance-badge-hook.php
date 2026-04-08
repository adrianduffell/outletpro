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

	public function test_display_badge_using_custom_single_product_hook_name(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		add_filter(
			'wc_clearance_badge_single_product_hook',
			static function () {
				return 'foo_hook';
			}
		);
		$product = WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		$GLOBALS['post'] = get_post( $product->get_id() );
		init_woocommerce_template_hooks();

		// Expect.
		$this->expectOutputRegex( '/wc-clearance-badge/' );

		// Act.
		do_action( 'foo_hook' );
	}

	public function test_display_badge_using_custom_single_product_hook_priority(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		add_filter(
			'wc_clearance_badge_single_product_priority',
			static function () {
				return 1;
			}
		);
		$product = WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		$GLOBALS['post'] = get_post( $product->get_id() );
		init_woocommerce_template_hooks();

		// Expect.
		$this->expectOutputRegex( '/wc-clearance-badge(?=.*<h1)/s' ); // Badge appears before the product title.

		// Act.
		do_action( 'woocommerce_single_product_summary' );
	}

	public function test_badge_single_product_hook_throws_on_non_string(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		add_filter(
			'wc_clearance_badge_single_product_hook',
			static function () {
				return 123;
			}
		);

		// Expect.
		$this->expectException( InvalidArgumentException::class );

		// Act.
		init_woocommerce_template_hooks();
	}

	public function test_badge_single_product_hook_throws_on_empty_string(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		add_filter(
			'wc_clearance_badge_single_product_hook',
			static function () {
				return '';
			}
		);

		// Expect.
		$this->expectException( InvalidArgumentException::class );

		// Act.
		init_woocommerce_template_hooks();
	}

	public function test_badge_single_product_priority_throws_on_non_integer(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		add_filter(
			'wc_clearance_badge_single_product_priority',
			static function () {
				return '6';
			}
		);

		// Expect.
		$this->expectException( InvalidArgumentException::class );

		// Act.
		init_woocommerce_template_hooks();
	}
}
