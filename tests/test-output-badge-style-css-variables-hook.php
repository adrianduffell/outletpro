<?php
/**
 * Tests for output_badge_style_css_variables_hook().
 *
 * @package OutletPro
 */

use function OutletPro\deinit_enqueue;
use function OutletPro\enqueue_init;
use const OutletPro\OUTLET_BADGE_BG_COLOR_OPTION;
use const OutletPro\OUTLET_BADGE_BORDER_COLOR_OPTION;
use const OutletPro\OUTLET_BADGE_BORDER_RADIUS_OPTION;
use const OutletPro\OUTLET_BADGE_BORDER_STYLE_OPTION;
use const OutletPro\OUTLET_BADGE_BORDER_WIDTH_OPTION;
use const OutletPro\OUTLET_BADGE_DENSITY_OPTION;
use const OutletPro\OUTLET_BADGE_FONT_WEIGHT_OPTION;
use const OutletPro\OUTLET_BADGE_LABEL_OPTION;
use const OutletPro\OUTLET_BADGE_SCALE_OPTION;
use const OutletPro\OUTLET_BADGE_TEXT_COLOR_OPTION;

class Test_Output_Badge_Style_Css_Variables_Hook extends WP_UnitTestCase {

	public function test_outputs_badge_style_css_variables_in_wp_head(): void {
		// Arrange.
		update_option( OUTLET_BADGE_BG_COLOR_OPTION, '#FF0000' );
		update_option( OUTLET_BADGE_TEXT_COLOR_OPTION, '#00FF00' );
		update_option( OUTLET_BADGE_BORDER_COLOR_OPTION, '#123456' );
		update_option( OUTLET_BADGE_BORDER_STYLE_OPTION, 'solid' );
		update_option( OUTLET_BADGE_BORDER_WIDTH_OPTION, '2px' );
		update_option( OUTLET_BADGE_BORDER_RADIUS_OPTION, '4px' );
		update_option( OUTLET_BADGE_FONT_WEIGHT_OPTION, '700' );
		update_option( OUTLET_BADGE_SCALE_OPTION, 140 );
		update_option( OUTLET_BADGE_DENSITY_OPTION, 80 );

		deinit_enqueue();
		enqueue_init();

		// Act.
		ob_start();
		do_action( 'wp_head' );
		$output = ob_get_clean();

		// Assert.
		$this->assertStringContainsString( '--outletpro-badge-bg-color: #FF0000', $output );
		$this->assertStringContainsString( '--outletpro-badge-text-color: #00FF00', $output );
		$this->assertStringContainsString( '--outletpro-badge-border-color: #123456', $output );
		$this->assertStringContainsString( '--outletpro-badge-border-style: solid', $output );
		$this->assertStringContainsString( '--outletpro-badge-border-width: 2px', $output );
		$this->assertStringContainsString( '--outletpro-badge-border-radius: 4px', $output );
		$this->assertStringContainsString( '--outletpro-badge-font-weight: 700', $output );
		$this->assertStringContainsString( '--outletpro-badge-scale: 140', $output );
		$this->assertStringContainsString( '--outletpro-badge-density: 80', $output );
	}

	public function test_uses_unset_when_setting_value_is_empty(): void {
		// Arrange.
		update_option( OUTLET_BADGE_BG_COLOR_OPTION, '' );
		deinit_enqueue();
		enqueue_init();

		// Assert.
		$this->expectOutputRegex( '/--outletpro-badge-bg-color: unset/' );

		// Act.
		do_action( 'wp_head' );
	}

	public function test_outputs_badge_label_css_variable_in_wp_head(): void {
		// Arrange.
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Sale' );
		deinit_enqueue();
		enqueue_init();

		// Act.
		ob_start();
		do_action( 'wp_head' );
		$output = ob_get_clean();

		// Assert.
		$this->assertStringContainsString( '--outletpro-badge-label: "Sale"', $output );

		delete_option( OUTLET_BADGE_LABEL_OPTION );
	}

	public function test_outputs_badge_label_none_when_empty(): void {
		// Arrange.
		delete_option( OUTLET_BADGE_LABEL_OPTION );
		deinit_enqueue();
		enqueue_init();

		// Act.
		ob_start();
		do_action( 'wp_head' );
		$output = ob_get_clean();

		// Assert.
		$this->assertStringContainsString( '--outletpro-badge-label: none', $output );
	}

	public function test_outputs_badge_label_none_when_set_to_empty_string(): void {
		// Arrange.
		update_option( OUTLET_BADGE_LABEL_OPTION, '' );
		deinit_enqueue();
		enqueue_init();

		// Act.
		ob_start();
		do_action( 'wp_head' );
		$output = ob_get_clean();

		// Assert.
		$this->assertStringContainsString( '--outletpro-badge-label: none', $output );

		delete_option( OUTLET_BADGE_LABEL_OPTION );
	}

	public function test_outputs_badge_label_escapes_double_quotes(): void {
		// Arrange.
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Big "Clearance"' );
		deinit_enqueue();
		enqueue_init();

		// Act.
		ob_start();
		do_action( 'wp_head' );
		$output = ob_get_clean();

		// Assert.
		$this->assertStringContainsString( '--outletpro-badge-label: "Big \"Clearance\""', $output );

		delete_option( OUTLET_BADGE_LABEL_OPTION );
	}

	public function test_outputs_badge_label_escapes_backslashes_and_preserves_emoji(): void {
		// Arrange.
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Sale \ Today 🔥' );
		deinit_enqueue();
		enqueue_init();

		// Act.
		ob_start();
		do_action( 'wp_head' );
		$output = ob_get_clean();

		// Assert.
		$this->assertStringContainsString( '--outletpro-badge-label: "Sale \\\\ Today 🔥"', $output );

		delete_option( OUTLET_BADGE_LABEL_OPTION );
	}
}
