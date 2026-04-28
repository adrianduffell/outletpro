<?php
/**
 * Tests for enqueue_cart_styles_hook().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\deinit_enqueue;
use function WC_Clearance\enqueue_init;
use const WC_Clearance\CLEARANCE_BADGE_LABEL_OPTION;

class Test_Enqueue_Cart_Styles_Hook extends WP_UnitTestCase {

	public function test_enqueues_cart_style(): void {
		// Arrange.
		deinit_enqueue();
		enqueue_init();

		// Act.
		do_action( 'wp_enqueue_scripts' );

		// Assert.
		$this->assertTrue( wp_style_is( 'wc-clearance-cart-badge', 'enqueued' ) );
	}

	public function test_inline_css_includes_label_from_settings(): void {
		// Arrange.
		update_option( CLEARANCE_BADGE_LABEL_OPTION, 'Sale' );
		deinit_enqueue();
		enqueue_init();

		// Act.
		do_action( 'wp_enqueue_scripts' );

		// Assert.
		$after = wp_styles()->get_data( 'wc-clearance-cart-badge', 'after' );
		$this->assertStringContainsString( '--wc-clearance-badge-label: "Sale"', implode( '', (array) $after ) );

		delete_option( CLEARANCE_BADGE_LABEL_OPTION );
	}

	public function test_inline_css_label_escapes_double_quotes(): void {
		// Arrange.
		update_option( CLEARANCE_BADGE_LABEL_OPTION, 'Big "Clearance"' );
		deinit_enqueue();
		enqueue_init();

		// Act.
		do_action( 'wp_enqueue_scripts' );

		// Assert.
		$after = wp_styles()->get_data( 'wc-clearance-cart-badge', 'after' );
		$this->assertStringContainsString( '--wc-clearance-badge-label: "Big \"Clearance\""', implode( '', (array) $after ) );

		delete_option( CLEARANCE_BADGE_LABEL_OPTION );
	}

	public function test_inline_css_label_escapes_backslashes_and_preserves_emoji(): void {
		// Arrange.
		update_option( CLEARANCE_BADGE_LABEL_OPTION, 'Sale \ Today 🔥' );
		deinit_enqueue();
		enqueue_init();

		// Act.
		do_action( 'wp_enqueue_scripts' );

		// Assert.
		$after = wp_styles()->get_data( 'wc-clearance-cart-badge', 'after' );
		$this->assertStringContainsString( '--wc-clearance-badge-label: "Sale \\\\ Today 🔥"', implode( '', (array) $after ) );

		delete_option( CLEARANCE_BADGE_LABEL_OPTION );
	}
}
