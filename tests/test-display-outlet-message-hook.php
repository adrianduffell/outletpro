<?php
/**
 * Tests for display_outlet_message_hook().
 *
 * @package OutletPro
 */

use function OutletPro\add_to_outlet;
use function OutletPro\init_woocommerce_template_hooks;
use function OutletPro\register_outlet_status_taxonomy;
use function OutletPro\seed_outlet_status_taxonomy;
use const OutletPro\OUTLET_MESSAGE_OPTION;

class Test_Display_Outlet_Message_Hook extends WP_UnitTestCase {

	public function test_hook_is_registered_after_init_woocommerce_template_hooks(): void {
		// Arrange.
		remove_action( 'woocommerce_product_meta_start', 'OutletPro\display_outlet_message_hook', 1 );

		// Act.
		init_woocommerce_template_hooks();

		// Assert.
		$this->assertSame( 1, has_action( 'woocommerce_product_meta_start', 'OutletPro\display_outlet_message_hook' ) );
	}

	public function test_displays_message_for_outlet_product(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_MESSAGE_OPTION, 'Not eligible for change of mind returns' );
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$GLOBALS['post'] = get_post( $product->get_id() );
		init_woocommerce_template_hooks();

		// Expect.
		$this->expectOutputRegex( '/outletpro-message/' );

		// Act.
		do_action( 'woocommerce_product_meta_start' );
	}

	public function test_message_contains_outlet_text(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_MESSAGE_OPTION, 'Not eligible for change of mind returns' );
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$GLOBALS['post'] = get_post( $product->get_id() );
		init_woocommerce_template_hooks();

		// Expect.
		$this->expectOutputRegex( '/Not eligible for change of mind returns/' );

		// Act.
		do_action( 'woocommerce_product_meta_start' );
	}

	public function test_does_not_display_message_when_option_is_empty(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_MESSAGE_OPTION, '' );
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$GLOBALS['post'] = get_post( $product->get_id() );
		init_woocommerce_template_hooks();

		// Expect.
		$this->expectOutputRegex( '/^(?!.*outletpro-message).*/s' ); // Does not contain the outlet message.

		// Act.
		do_action( 'woocommerce_product_meta_start' );
	}

	public function test_does_not_display_message_when_option_does_not_exist(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		delete_option( OUTLET_MESSAGE_OPTION );
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$GLOBALS['post'] = get_post( $product->get_id() );
		init_woocommerce_template_hooks();

		// Expect.
		$this->expectOutputRegex( '/^(?!.*outletpro-message).*/s' ); // Does not contain the outlet message.

		// Act.
		do_action( 'woocommerce_product_meta_start' );
	}

	public function test_does_not_display_message_for_non_outlet_product(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product         = \WC_Helper_Product::create_simple_product();
		$GLOBALS['post'] = get_post( $product->get_id() );
		init_woocommerce_template_hooks();

		// Expect.
		$this->expectOutputRegex( '/^(?!.*outletpro-message).*/s' ); // Does not contain the outlet message.

		// Act.
		do_action( 'woocommerce_product_meta_start' );
	}

	public function test_does_not_display_message_when_post_is_not_a_product(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$post_id         = self::factory()->post->create();
		$GLOBALS['post'] = get_post( $post_id );
		init_woocommerce_template_hooks();

		// Expect.
		$this->expectOutputRegex( '/^(?!.*outletpro-message).*/s' ); // Does not contain the outlet message.

		// Act.
		do_action( 'woocommerce_product_meta_start' );
	}
}
