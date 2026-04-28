<?php
/**
 * Tests for output_badge_style_css_variables_hook().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\deinit_enqueue;
use function WC_Clearance\enqueue_init;
use const WC_Clearance\CLEARANCE_BADGE_BG_COLOR_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_BORDER_COLOR_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_BORDER_RADIUS_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_BORDER_STYLE_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_BORDER_WIDTH_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_FONT_SIZE_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_FONT_WEIGHT_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_PADDING_BOTTOM_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_PADDING_LEFT_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_PADDING_RIGHT_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_PADDING_TOP_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_TEXT_COLOR_OPTION;

class Test_Output_Badge_Style_Css_Variables_Hook extends WP_UnitTestCase {

	public function test_outputs_badge_style_css_variables_in_wp_head(): void {
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

		deinit_enqueue();
		enqueue_init();

		// Assert.
		$this->expectOutputRegex( '/--wc-clearance-badge-bg-color: #FF0000/' );
		$this->expectOutputRegex( '/--wc-clearance-badge-text-color: #00FF00/' );
		$this->expectOutputRegex( '/--wc-clearance-badge-border-color: #123456/' );
		$this->expectOutputRegex( '/--wc-clearance-badge-border-style: solid/' );
		$this->expectOutputRegex( '/--wc-clearance-badge-border-width: 2px/' );
		$this->expectOutputRegex( '/--wc-clearance-badge-border-radius: 4px/' );
		$this->expectOutputRegex( '/--wc-clearance-badge-font-size: 1rem/' );
		$this->expectOutputRegex( '/--wc-clearance-badge-font-weight: 700/' );
		$this->expectOutputRegex( '/--wc-clearance-badge-padding-top: 1px/' );
		$this->expectOutputRegex( '/--wc-clearance-badge-padding-right: 2px/' );
		$this->expectOutputRegex( '/--wc-clearance-badge-padding-bottom: 3px/' );
		$this->expectOutputRegex( '/--wc-clearance-badge-padding-left: 4px/' );

		// Act.
		do_action( 'wp_head' );
	}

	public function test_uses_unset_when_setting_value_is_empty(): void {
		// Arrange.
		update_option( CLEARANCE_BADGE_BG_COLOR_OPTION, '' );
		deinit_enqueue();
		enqueue_init();

		// Assert.
		$this->expectOutputRegex( '/--wc-clearance-badge-bg-color: unset/' );

		// Act.
		do_action( 'wp_head' );
	}
}
