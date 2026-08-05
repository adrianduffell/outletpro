<?php
/**
 * Tests for display_outlet_badge_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\init_woocommerce_template_hooks;
use function OutletPro\register_outlet_status_taxonomy;
use function OutletPro\seed_outlet_status_taxonomy;
use const OutletPro\OUTLET_BADGE_LABEL_OPTION;

class Test_Display_Outlet_Badge_Hook extends WP_UnitTestCase {

	public function test_outputs_badge_html_for_outlet_product(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Clearance' );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$GLOBALS['post'] = get_post( $product->get_id() );
		init_woocommerce_template_hooks();

		// Expect.
		$this->expectOutputRegex( '/<p[^>]+class="[^"]*outletpro-badge/' );

		// Act.
		do_action( 'woocommerce_single_product_summary' );
	}

	public function test_outputs_nothing_for_non_outlet_product(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product         = WC_Helper_Product::create_simple_product();
		$GLOBALS['post'] = get_post( $product->get_id() );
		init_woocommerce_template_hooks();

		// Expect.
		$this->expectOutputRegex( '/^(?!.*outletpro-badge).*/s' ); // Does not contain the outlet badge.

		// Act.
		do_action( 'woocommerce_single_product_summary' );
	}

	public function test_display_badge_using_custom_single_product_hook_name(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		add_filter(
			'outletpro_badge_single_product_hook',
			static function () {
				return 'foo_hook';
			}
		);
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Clearance' );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$GLOBALS['post'] = get_post( $product->get_id() );
		init_woocommerce_template_hooks();

		// Expect.
		$this->expectOutputRegex( '/outletpro-badge/' );

		// Act.
		do_action( 'foo_hook' );
	}

	public function test_display_badge_using_custom_single_product_hook_priority(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		add_filter(
			'outletpro_badge_single_product_priority',
			static function () {
				return 1;
			}
		);
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Clearance' );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$GLOBALS['post'] = get_post( $product->get_id() );
		init_woocommerce_template_hooks();

		// Expect.
		$this->expectOutputRegex( '/outletpro-badge(?=.*<h1)/s' ); // Badge appears before the product title.

		// Act.
		do_action( 'woocommerce_single_product_summary' );
	}

	public function test_badge_single_product_hook_throws_on_non_string(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded
		// Arrange.
		add_filter(
			'outletpro_badge_single_product_hook',
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
			'outletpro_badge_single_product_hook',
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
			'outletpro_badge_single_product_priority',
			static function () {
				return '6';
			}
		);

		// Expect.
		$this->expectException( InvalidArgumentException::class );

		// Act.
		init_woocommerce_template_hooks();
	}

	public function test_outputs_nothing_when_label_is_empty(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_BADGE_LABEL_OPTION, '' );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$GLOBALS['post'] = get_post( $product->get_id() );
		init_woocommerce_template_hooks();

		// Expect.
		$this->expectOutputRegex( '/^(?!.*outletpro-badge).*/s' ); // Does not contain the outlet badge.

		// Act.
		do_action( 'woocommerce_single_product_summary' );
	}
}
