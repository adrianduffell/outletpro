<?php
/**
 * Tests for enqueue_classic_styles_hook().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\init_woocommerce_template_hooks;

class Test_Enqueue_Classic_Styles_Hook extends WP_UnitTestCase {

	public function test_hook_is_registered_after_init_woocommerce_template_hooks(): void {
		// Arrange.
		switch_theme( 'storefront' );
		remove_action( 'wp_enqueue_scripts', 'WC_Clearance\enqueue_classic_styles_hook' );

		// Act.
		init_woocommerce_template_hooks();

		// Assert.
		$this->assertNotFalse( has_action( 'wp_enqueue_scripts', 'WC_Clearance\enqueue_classic_styles_hook' ) );
	}

	public function test_hook_is_not_registered_for_block_themes(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		remove_action( 'wp_enqueue_scripts', 'WC_Clearance\enqueue_classic_styles_hook' );

		// Act.
		init_woocommerce_template_hooks();

		// Assert.
		$this->assertFalse( has_action( 'wp_enqueue_scripts', 'WC_Clearance\enqueue_classic_styles_hook' ) );
	}

	public function test_classic_css_is_registered_when_hook_fires(): void {
		// Arrange.
		switch_theme( 'storefront' );
		init_woocommerce_template_hooks();

		// Act.
		do_action( 'wp_enqueue_scripts' );

		// Assert.
		$this->assertTrue( wp_style_is( 'wc-clearance', 'registered' ) );
	}
}
