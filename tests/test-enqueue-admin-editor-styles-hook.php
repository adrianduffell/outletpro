<?php
/**
 * Tests for enqueue_admin_editor_styles_hook().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\deinit_enqueue;
use function WC_Clearance\enqueue_init;
use const WC_Clearance\CLEARANCE_BADGE_LABEL_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_BG_COLOR_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_TEXT_COLOR_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_BORDER_COLOR_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_BORDER_STYLE_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_BORDER_WIDTH_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_BORDER_RADIUS_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_FONT_SIZE_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_FONT_WEIGHT_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_PADDING_TOP_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_PADDING_RIGHT_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_PADDING_BOTTOM_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_PADDING_LEFT_OPTION;

class Test_Enqueue_Admin_Editor_Styles_Hook extends WP_UnitTestCase {

	public function test_enqueues_admin_editor_style_in_admin(): void {
		// Arrange.
		deinit_enqueue();
		enqueue_init();
		set_current_screen( 'dashboard' );

		// Act.
		do_action( 'enqueue_block_assets' );

		// Assert.
		$this->assertTrue( wp_style_is( 'wc-clearance-admin-editor', 'enqueued' ) );
	}

	public function test_does_not_enqueue_admin_editor_style_on_front_end(): void {
		// Arrange.
		deinit_enqueue();
		enqueue_init();
		set_current_screen( 'front' );

		// Act.
		do_action( 'enqueue_block_assets' );

		// Assert.
		$this->assertFalse( wp_style_is( 'wc-clearance-admin-editor', 'enqueued' ) );
	}

	public function test_inline_css_includes_label_from_settings(): void {
		// Arrange.
		update_option( CLEARANCE_BADGE_LABEL_OPTION, 'Sale' );
		set_current_screen( 'dashboard' );
		deinit_enqueue();
		enqueue_init();

		// Act.
		do_action( 'enqueue_block_assets' );

		// Assert.
		$after = wp_styles()->get_data( 'wc-clearance-admin-editor', 'after' );
		$this->assertStringContainsString( '--wc-clearance-badge-label: "Sale"', implode( '', (array) $after ) );

		delete_option( CLEARANCE_BADGE_LABEL_OPTION );
	}

	public function test_inline_css_includes_badge_style_vars(): void {
		// Arrange.
		update_option( CLEARANCE_BADGE_BG_COLOR_OPTION, '#FF0000' );
		update_option( CLEARANCE_BADGE_TEXT_COLOR_OPTION, '#00FF00' );
		update_option( CLEARANCE_BADGE_BORDER_COLOR_OPTION, '#123456' );
		update_option( CLEARANCE_BADGE_BORDER_STYLE_OPTION, 'solid' );
		update_option( CLEARANCE_BADGE_BORDER_WIDTH_OPTION, '2px' );
		update_option( CLEARANCE_BADGE_BORDER_RADIUS_OPTION, '4px' );
		update_option( CLEARANCE_BADGE_FONT_SIZE_OPTION, '1rem' );
		update_option( CLEARANCE_BADGE_FONT_WEIGHT_OPTION, '700' );
		update_option( CLEARANCE_BADGE_PADDING_TOP_OPTION, '1px' );
		update_option( CLEARANCE_BADGE_PADDING_RIGHT_OPTION, '2px' );
		update_option( CLEARANCE_BADGE_PADDING_BOTTOM_OPTION, '3px' );
		update_option( CLEARANCE_BADGE_PADDING_LEFT_OPTION, '4px' );
		set_current_screen( 'dashboard' );
		deinit_enqueue();
		enqueue_init();

		// Act.
		do_action( 'enqueue_block_assets' );

		// Assert.
		$after = wp_styles()->get_data( 'wc-clearance-admin-editor', 'after' );
		$css   = implode( '', (array) $after );
		$this->assertStringContainsString( '--wc-clearance-badge-bg-color: #FF0000', $css );
		$this->assertStringContainsString( '--wc-clearance-badge-text-color: #00FF00', $css );
		$this->assertStringContainsString( '--wc-clearance-badge-border-color: #123456', $css );
		$this->assertStringContainsString( '--wc-clearance-badge-border-style: solid', $css );
		$this->assertStringContainsString( '--wc-clearance-badge-border-width: 2px', $css );
		$this->assertStringContainsString( '--wc-clearance-badge-border-radius: 4px', $css );
		$this->assertStringContainsString( '--wc-clearance-badge-font-size: 1rem', $css );
		$this->assertStringContainsString( '--wc-clearance-badge-font-weight: 700', $css );
		$this->assertStringContainsString( '--wc-clearance-badge-padding-top: 1px', $css );
		$this->assertStringContainsString( '--wc-clearance-badge-padding-right: 2px', $css );
		$this->assertStringContainsString( '--wc-clearance-badge-padding-bottom: 3px', $css );
		$this->assertStringContainsString( '--wc-clearance-badge-padding-left: 4px', $css );

		delete_option( CLEARANCE_BADGE_BG_COLOR_OPTION );
		delete_option( CLEARANCE_BADGE_TEXT_COLOR_OPTION );
		delete_option( CLEARANCE_BADGE_BORDER_COLOR_OPTION );
		delete_option( CLEARANCE_BADGE_BORDER_STYLE_OPTION );
		delete_option( CLEARANCE_BADGE_BORDER_WIDTH_OPTION );
		delete_option( CLEARANCE_BADGE_BORDER_RADIUS_OPTION );
		delete_option( CLEARANCE_BADGE_FONT_SIZE_OPTION );
		delete_option( CLEARANCE_BADGE_FONT_WEIGHT_OPTION );
		delete_option( CLEARANCE_BADGE_PADDING_TOP_OPTION );
		delete_option( CLEARANCE_BADGE_PADDING_RIGHT_OPTION );
		delete_option( CLEARANCE_BADGE_PADDING_BOTTOM_OPTION );
		delete_option( CLEARANCE_BADGE_PADDING_LEFT_OPTION );
	}

	public function test_inline_css_uses_unset_when_badge_style_value_is_empty(): void {
		// Arrange.
		update_option( CLEARANCE_BADGE_BG_COLOR_OPTION, '' );
		set_current_screen( 'dashboard' );
		deinit_enqueue();
		enqueue_init();

		// Act.
		do_action( 'enqueue_block_assets' );

		// Assert.
		$after = wp_styles()->get_data( 'wc-clearance-admin-editor', 'after' );
		$this->assertStringContainsString( '--wc-clearance-badge-bg-color: unset', implode( '', (array) $after ) );

		delete_option( CLEARANCE_BADGE_BG_COLOR_OPTION );
	}
}
