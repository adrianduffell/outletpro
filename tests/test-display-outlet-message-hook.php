<?php
/**
 * Tests for display_outlet_message_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\init_taxonomies;
use function OutletPro\init_woocommerce_template_hooks;
use function OutletPro\seed_outlet_status_taxonomy;
use function OutletPro\seed_theme_mods;
use const OutletPro\OUTLET_MESSAGE_OPTION;
use const OutletPro\SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD;

class Test_Display_Outlet_Message_Hook extends WP_UnitTestCase {

	// phpcs:ignore Generic.Commenting.DocComment.MissingShort -- Only test data and parameter types are documented.
	/**
	 * @dataProvider invalid_template_hooks
	 * @param mixed $hook Invalid theme mod value.
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
	 */
	public function test_rejects_invalid_template_hook( $hook ): void {
		// Arrange.
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD, $hook );

		// Expect.
		$this->expectException( InvalidArgumentException::class );

		// Act.
		init_woocommerce_template_hooks();
	}

	/**
	 * Malformed template hook settings.
	 *
	 * @return array<int, array<int, mixed>>
	 */
	public static function invalid_template_hooks(): array {
		return array(
			array( 'woocommerce_product_meta_start' ),
			array( array( 'woocommerce_product_meta_start' ) ),
			array(
				array(
					'name'     => 'woocommerce_product_meta_start',
					'priority' => 1,
				),
			),
			array( array( '', 1 ) ),
			array( array( 123, 1 ) ),
			array( array( 'woocommerce_product_meta_start', '1' ) ),
		);
	}

	public function test_hook_is_registered_after_init_woocommerce_template_hooks(): void {
		// Arrange.
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD );
		remove_action( 'woocommerce_single_product_summary', 'OutletPro\display_outlet_message_hook', 11 );

		// Act.
		init_woocommerce_template_hooks();

		// Assert.
		$this->assertSame( 11, has_action( 'woocommerce_single_product_summary', 'OutletPro\display_outlet_message_hook' ) );
	}

	public function test_displays_message_for_outlet_product(): void {
		// Arrange.
		switch_theme( 'storefront' );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD );
		seed_theme_mods();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_MESSAGE_OPTION, 'Not eligible for change of mind returns' );
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$GLOBALS['post'] = get_post( $product->get_id() );
		init_woocommerce_template_hooks();

		// Expect.
		$this->expectOutputString( '<p class="outletpro-message">Not eligible for change of mind returns</p>' );

		// Act.
		do_action( 'woocommerce_single_product_summary' );
	}

	public function test_message_contains_outlet_text(): void {
		// Arrange.
		switch_theme( 'storefront' );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD );
		seed_theme_mods();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_MESSAGE_OPTION, 'Not eligible for change of mind returns' );
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$GLOBALS['post'] = get_post( $product->get_id() );
		init_woocommerce_template_hooks();

		// Expect.
		$this->expectOutputRegex( '/Not eligible for change of mind returns/' );

		// Act.
		do_action( 'woocommerce_single_product_summary' );
	}

	public function test_does_not_display_message_when_option_is_empty(): void {
		// Arrange.
		switch_theme( 'storefront' );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD );
		seed_theme_mods();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_MESSAGE_OPTION, '' );
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$GLOBALS['post'] = get_post( $product->get_id() );
		init_woocommerce_template_hooks();

		// Expect.
		$this->expectOutputRegex( '/^(?!.*outletpro-message).*/s' ); // Does not contain the outlet message.

		// Act.
		do_action( 'woocommerce_single_product_summary' );
	}

	public function test_does_not_display_message_when_option_does_not_exist(): void {
		// Arrange.
		switch_theme( 'storefront' );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD );
		seed_theme_mods();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		delete_option( OUTLET_MESSAGE_OPTION );
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$GLOBALS['post'] = get_post( $product->get_id() );
		init_woocommerce_template_hooks();

		// Expect.
		$this->expectOutputRegex( '/^(?!.*outletpro-message).*/s' ); // Does not contain the outlet message.

		// Act.
		do_action( 'woocommerce_single_product_summary' );
	}

	public function test_does_not_display_message_for_non_outlet_product(): void {
		// Arrange.
		switch_theme( 'storefront' );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD );
		seed_theme_mods();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		$product         = \WC_Helper_Product::create_simple_product();
		$GLOBALS['post'] = get_post( $product->get_id() );
		init_woocommerce_template_hooks();

		// Expect.
		$this->expectOutputRegex( '/^(?!.*outletpro-message).*/s' ); // Does not contain the outlet message.

		// Act.
		do_action( 'woocommerce_single_product_summary' );
	}

	public function test_does_not_display_message_when_post_is_not_a_product(): void {
		// Arrange.
		switch_theme( 'storefront' );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD );
		seed_theme_mods();
		init_taxonomies();
		seed_outlet_status_taxonomy();
		$post_id         = self::factory()->post->create();
		$GLOBALS['post'] = get_post( $post_id );
		init_woocommerce_template_hooks();

		// Expect.
		$this->expectOutputRegex( '/^(?!.*outletpro-message).*/s' ); // Does not contain the outlet message.

		// Act.
		do_action( 'woocommerce_single_product_summary' );
	}
}
