<?php
/**
 * Tests for deinit_settings().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_settings;
use function OutletPro\init_settings;
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
use const OutletPro\OUTLET_MARKDOWN_LEVELS_OPTION;
use const OutletPro\OUTLET_MESSAGE_OPTION;
use const OutletPro\OUTLET_PAGE_OPTION;
use const OutletPro\OUTLET_PRICE_LEVELS_OPTION;

class Test_Deinit_Settings extends WP_UnitTestCase {

	public function test_unregisters_outlet_settings(): void {
		// Arrange.
		init_settings();
		$options = array_fill_keys(
			array(
				OUTLET_PAGE_OPTION,
				OUTLET_MARKDOWN_LEVELS_OPTION,
				OUTLET_PRICE_LEVELS_OPTION,
				OUTLET_BADGE_LABEL_OPTION,
				OUTLET_BADGE_TEXT_COLOR_OPTION,
				OUTLET_BADGE_BG_COLOR_OPTION,
				OUTLET_BADGE_BORDER_COLOR_OPTION,
				OUTLET_BADGE_BORDER_STYLE_OPTION,
				OUTLET_BADGE_BORDER_WIDTH_OPTION,
				OUTLET_BADGE_BORDER_RADIUS_OPTION,
				OUTLET_BADGE_FONT_WEIGHT_OPTION,
				OUTLET_BADGE_SCALE_OPTION,
				OUTLET_BADGE_DENSITY_OPTION,
				OUTLET_MESSAGE_OPTION,
			),
			true
		);

		// Act.
		deinit_settings();

		// Assert.
		$this->assertSame( array(), array_intersect_key( get_registered_settings(), $options ) );
	}

	public function test_preserves_unrelated_settings(): void {
		// Arrange.
		init_settings();
		register_setting( 'outletpro', 'unrelated_setting' );

		// Act.
		deinit_settings();

		// Assert.
		$this->assertArrayHasKey( 'unrelated_setting', get_registered_settings() );
	}

	public function test_preserves_saved_values(): void {
		// Arrange.
		init_settings();
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Clearance' );

		// Act.
		deinit_settings();

		// Assert.
		$this->assertSame( 'Clearance', get_option( OUTLET_BADGE_LABEL_OPTION ) );
	}

	public function test_removes_setting_filters(): void {
		// Arrange.
		init_settings();

		// Act.
		deinit_settings();

		// Assert.
		$this->assertFalse( has_filter( 'sanitize_option_' . OUTLET_PRICE_LEVELS_OPTION, 'OutletPro\sanitize_integer_array' ) );
		$this->assertFalse( has_filter( 'default_option_' . OUTLET_PRICE_LEVELS_OPTION, 'filter_default_option' ) );
	}

	public function test_removes_search_index_sync_hooks(): void {
		// Arrange.
		init_settings();

		// Act.
		deinit_settings();

		// Assert.
		$this->assertFalse( has_action( 'update_option_' . OUTLET_PRICE_LEVELS_OPTION, 'OutletPro\sync_outlet_price_levels_hook' ) );
		$this->assertFalse( has_action( 'add_option_' . OUTLET_PRICE_LEVELS_OPTION, 'OutletPro\sync_outlet_price_levels_hook' ) );
		$this->assertFalse( has_action( 'delete_option_' . OUTLET_PRICE_LEVELS_OPTION, 'OutletPro\sync_outlet_price_levels_hook' ) );
		$this->assertFalse( has_action( 'update_option_' . OUTLET_MARKDOWN_LEVELS_OPTION, 'OutletPro\sync_outlet_markdown_levels_hook' ) );
		$this->assertFalse( has_action( 'add_option_' . OUTLET_MARKDOWN_LEVELS_OPTION, 'OutletPro\sync_outlet_markdown_levels_hook' ) );
		$this->assertFalse( has_action( 'delete_option_' . OUTLET_MARKDOWN_LEVELS_OPTION, 'OutletPro\sync_outlet_markdown_levels_hook' ) );
	}
}
