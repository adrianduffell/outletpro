<?php
/**
 * Tests for display_clearance_badge_hook().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_to_clearance;
use function WC_Clearance\classic_theme_init;
use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\seed_clearance_status_taxonomy;

class Test_Display_Clearance_Badge_Hook extends WP_UnitTestCase {

	public function test_hook_is_registered_after_classic_theme_init(): void {
		// Arrange.
		remove_action( 'woocommerce_single_product_summary', 'WC_Clearance\display_clearance_badge_hook', 15 );

		// Act.
		classic_theme_init();

		// Assert.
		$this->assertSame( 15, has_action( 'woocommerce_single_product_summary', 'WC_Clearance\display_clearance_badge_hook' ) );
	}

	public function test_outputs_badge_html_for_clearance_product(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		remove_all_actions( 'woocommerce_single_product_summary' );
		classic_theme_init();
		global $post;
		$post = get_post( $product->get_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $post );

		// Expect.
		$this->expectOutputRegex( '/wc-clearance-product-badge/' );

		// Act.
		do_action( 'woocommerce_single_product_summary' );

		wp_reset_postdata();
	}

	public function test_outputs_clearance_message_for_clearance_product(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		remove_all_actions( 'woocommerce_single_product_summary' );
		classic_theme_init();
		global $post;
		$post = get_post( $product->get_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $post );

		// Expect.
		$this->expectOutputRegex( '/Choose carefully! Clearance products are ineligible for returns\./' );

		// Act.
		do_action( 'woocommerce_single_product_summary' );

		wp_reset_postdata();
	}

	public function test_outputs_nothing_for_non_clearance_product(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		remove_all_actions( 'woocommerce_single_product_summary' );
		classic_theme_init();
		global $post;
		$post = get_post( $product->get_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $post );

		// Expect.
		$this->expectOutputString( '' );

		// Act.
		do_action( 'woocommerce_single_product_summary' );

		wp_reset_postdata();
	}
}
