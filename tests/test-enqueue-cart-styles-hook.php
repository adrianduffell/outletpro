<?php
/**
 * Tests for enqueue_cart_styles_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_enqueue;
use function OutletPro\enqueue_init;
use const OutletPro\OUTLET_BADGE_LABEL_OPTION;

class Test_Enqueue_Cart_Styles_Hook extends WP_UnitTestCase {

	public function test_enqueues_cart_style(): void {
		// Arrange.
		deinit_enqueue();
		enqueue_init();

		// Act.
		do_action( 'wp_enqueue_scripts' );

		// Assert.
		$this->assertTrue( wp_style_is( 'outletpro-cart-badge', 'enqueued' ) );
	}

	public function test_inline_css_label_outputs_none_when_empty(): void {
		// Arrange.
		delete_option( OUTLET_BADGE_LABEL_OPTION );
		deinit_enqueue();
		enqueue_init();

		// Act.
		do_action( 'wp_enqueue_scripts' );

		// Assert.
		$after = wp_styles()->get_data( 'outletpro-cart-badge', 'after' );
		$this->assertStringContainsString( '--outletpro-badge-label: none', implode( '', (array) $after ) );
	}

	public function test_inline_css_label_outputs_none_when_set_to_empty_string(): void {
		// Arrange.
		update_option( OUTLET_BADGE_LABEL_OPTION, '' );
		deinit_enqueue();
		enqueue_init();

		// Act.
		do_action( 'wp_enqueue_scripts' );

		// Assert.
		$after = wp_styles()->get_data( 'outletpro-cart-badge', 'after' );
		$this->assertStringContainsString( '--outletpro-badge-label: none', implode( '', (array) $after ) );

		delete_option( OUTLET_BADGE_LABEL_OPTION );
	}

	public function test_inline_css_includes_label_from_settings(): void {
		// Arrange.
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Sale' );
		deinit_enqueue();
		enqueue_init();

		// Act.
		do_action( 'wp_enqueue_scripts' );

		// Assert.
		$after = wp_styles()->get_data( 'outletpro-cart-badge', 'after' );
		$this->assertStringContainsString( '--outletpro-badge-label: "Sale"', implode( '', (array) $after ) );

		delete_option( OUTLET_BADGE_LABEL_OPTION );
	}

	public function test_inline_css_label_escapes_double_quotes(): void {
		// Arrange.
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Big "Clearance"' );
		deinit_enqueue();
		enqueue_init();

		// Act.
		do_action( 'wp_enqueue_scripts' );

		// Assert.
		$after = wp_styles()->get_data( 'outletpro-cart-badge', 'after' );
		$this->assertStringContainsString( '--outletpro-badge-label: "Big \"Clearance\""', implode( '', (array) $after ) );

		delete_option( OUTLET_BADGE_LABEL_OPTION );
	}

	public function test_inline_css_label_escapes_backslashes_and_preserves_emoji(): void {
		// Arrange.
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Sale \ Today 🔥' );
		deinit_enqueue();
		enqueue_init();

		// Act.
		do_action( 'wp_enqueue_scripts' );

		// Assert.
		$after = wp_styles()->get_data( 'outletpro-cart-badge', 'after' );
		$this->assertStringContainsString( '--outletpro-badge-label: "Sale \\\\ Today 🔥"', implode( '', (array) $after ) );

		delete_option( OUTLET_BADGE_LABEL_OPTION );
	}
}
