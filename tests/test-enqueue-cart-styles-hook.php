<?php
/**
 * Tests for enqueue_cart_styles_hook().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\enqueue_cart_styles_hook;
use const WC_Clearance\CLEARANCE_BADGE_BG_COLOR_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_TEXT_COLOR_OPTION;

class Test_Enqueue_Cart_Styles_Hook extends WP_UnitTestCase {

	public function test_enqueues_cart_style(): void {
		// Arrange.
		wp_deregister_style( 'wc-clearance-cart' );

		// Act.
		enqueue_cart_styles_hook();

		// Assert.
		$this->assertTrue( wp_style_is( 'wc-clearance-cart', 'enqueued' ) );
	}

	public function test_inline_css_includes_bg_color_from_settings(): void {
		// Arrange.
		update_option( CLEARANCE_BADGE_BG_COLOR_OPTION, '#FF0000' );
		wp_deregister_style( 'wc-clearance-cart' );

		// Act.
		enqueue_cart_styles_hook();

		// Assert.
		$after = wp_styles()->get_data( 'wc-clearance-cart', 'after' );
		$this->assertStringContainsString( '--wc-clearance-badge-bg-color: #FF0000', implode( '', (array) $after ) );

		delete_option( CLEARANCE_BADGE_BG_COLOR_OPTION );
	}

	public function test_inline_css_includes_text_color_from_settings(): void {
		// Arrange.
		update_option( CLEARANCE_BADGE_TEXT_COLOR_OPTION, '#00FF00' );
		wp_deregister_style( 'wc-clearance-cart' );

		// Act.
		enqueue_cart_styles_hook();

		// Assert.
		$after = wp_styles()->get_data( 'wc-clearance-cart', 'after' );
		$this->assertStringContainsString( '--wc-clearance-badge-text-color: #00FF00', implode( '', (array) $after ) );

		delete_option( CLEARANCE_BADGE_TEXT_COLOR_OPTION );
	}
}
