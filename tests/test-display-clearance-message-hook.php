<?php
/**
 * Tests for display_clearance_message_hook().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_to_clearance;
use function WC_Clearance\init_classic_themes;
use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\seed_clearance_status_taxonomy;

class Test_Display_Clearance_Message_Hook extends WP_UnitTestCase {

	public function test_displays_message_for_clearance_product(): void {
		// Arrange.
		switch_theme( 'storefront' );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		$GLOBALS['post'] = get_post( $product->get_id() );
		init_classic_themes();

		// Expect.
		$this->expectOutputRegex( '/wc-clearance-product-message/' );

		// Act.
		do_action( 'woocommerce_single_product_summary' );
	}

	public function test_message_contains_clearance_text(): void {
		// Arrange.
		switch_theme( 'storefront' );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		$GLOBALS['post'] = get_post( $product->get_id() );
		init_classic_themes();

		// Expect.
		$this->expectOutputRegex( '/Choose carefully! Clearance products are ineligible for returns\./' );

		// Act.
		do_action( 'woocommerce_single_product_summary' );
	}

	public function test_does_not_display_message_for_non_clearance_product(): void {
		// Arrange.
		switch_theme( 'storefront' );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product         = \WC_Helper_Product::create_simple_product();
		$GLOBALS['post'] = get_post( $product->get_id() );
		init_classic_themes();

		// Expect.
		$this->expectOutputRegex( '/^(?!.*wc-clearance-product-message).*/s' ); // Does not contain the clearance message.

		// Act.
		do_action( 'woocommerce_single_product_summary' );
	}

	public function test_does_not_display_message_when_post_is_not_a_product(): void {
		// Arrange.
		switch_theme( 'storefront' );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$post_id         = self::factory()->post->create();
		$GLOBALS['post'] = get_post( $post_id );
		init_classic_themes();

		// Expect.
		$this->expectOutputRegex( '/^(?!.*wc-clearance-product-message).*/s' ); // Does not contain the clearance message.

		// Act.
		do_action( 'woocommerce_single_product_summary' );
	}

	public function test_does_not_display_message_for_block_theme(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		$GLOBALS['post'] = get_post( $product->get_id() );
		init_classic_themes();

		// Expect.
		$this->expectOutputRegex( '/^(?!.*wc-clearance-product-message).*/s' ); // Does not contain the clearance message.

		// Act.
		do_action( 'woocommerce_single_product_summary' );
	}
}
