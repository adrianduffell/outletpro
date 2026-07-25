<?php
/**
 * Test the seed_settings function.
 *
 * @package OutletPro
 */

use function OutletPro\seed_settings;
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
use const OutletPro\OUTLET_MESSAGE_OPTION;

class Test_Seed_Settings extends WP_UnitTestCase {

	public function test_sets_badge_label_default_when_option_does_not_exist(): void {
		// Arrange.
		delete_option( OUTLET_BADGE_LABEL_OPTION );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( 'Last chance', get_option( OUTLET_BADGE_LABEL_OPTION ) );
	}

	public function test_does_not_overwrite_existing_badge_label_option(): void {
		// Arrange.
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Custom Label' );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( 'Custom Label', get_option( OUTLET_BADGE_LABEL_OPTION ) );
	}

	public function test_sets_stocks_last_message_for_non_us_store(): void {
		// Arrange.
		update_option( 'woocommerce_default_country', 'GB' );
		delete_option( OUTLET_MESSAGE_OPTION );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( 'Only while stocks last', get_option( OUTLET_MESSAGE_OPTION ) );
	}

	public function test_sets_supplies_last_message_for_us_store(): void {
		// Arrange.
		update_option( 'woocommerce_default_country', 'US' );
		delete_option( OUTLET_MESSAGE_OPTION );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( 'Only while supplies last', get_option( OUTLET_MESSAGE_OPTION ) );
	}

	public function test_sets_supplies_last_message_for_canadian_store(): void {
		// Arrange.
		update_option( 'woocommerce_default_country', 'CA' );
		delete_option( OUTLET_MESSAGE_OPTION );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( 'Only while supplies last', get_option( OUTLET_MESSAGE_OPTION ) );
	}

	public function test_does_not_overwrite_existing_message_option(): void {
		// Arrange.
		update_option( OUTLET_MESSAGE_OPTION, 'Custom message.' );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( 'Custom message.', get_option( OUTLET_MESSAGE_OPTION ) );
	}

	public function test_sets_badge_text_color_default_when_option_does_not_exist(): void {
		// Arrange.
		delete_option( OUTLET_BADGE_TEXT_COLOR_OPTION );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( '#FFFFFF', get_option( OUTLET_BADGE_TEXT_COLOR_OPTION ) );
	}

	public function test_does_not_overwrite_existing_badge_text_color_option(): void {
		// Arrange.
		update_option( OUTLET_BADGE_TEXT_COLOR_OPTION, '#000000' );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( '#000000', get_option( OUTLET_BADGE_TEXT_COLOR_OPTION ) );
	}

	public function test_sets_badge_bg_color_default_when_option_does_not_exist(): void {
		// Arrange.
		delete_option( OUTLET_BADGE_BG_COLOR_OPTION );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( '#F81240', get_option( OUTLET_BADGE_BG_COLOR_OPTION ) );
	}

	public function test_does_not_overwrite_existing_badge_bg_color_option(): void {
		// Arrange.
		update_option( OUTLET_BADGE_BG_COLOR_OPTION, '#FF0000' );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( '#FF0000', get_option( OUTLET_BADGE_BG_COLOR_OPTION ) );
	}

	public function test_sets_badge_border_radius_default_when_option_does_not_exist(): void {
		// Arrange.
		delete_option( OUTLET_BADGE_BORDER_RADIUS_OPTION );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( '2px', get_option( OUTLET_BADGE_BORDER_RADIUS_OPTION ) );
	}

	public function test_sets_badge_border_color_default_when_option_does_not_exist(): void {
		// Arrange.
		delete_option( OUTLET_BADGE_BORDER_COLOR_OPTION );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( '', get_option( OUTLET_BADGE_BORDER_COLOR_OPTION ) );
	}

	public function test_does_not_overwrite_existing_badge_border_color_option(): void {
		// Arrange.
		update_option( OUTLET_BADGE_BORDER_COLOR_OPTION, '#000000' );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( '#000000', get_option( OUTLET_BADGE_BORDER_COLOR_OPTION ) );
	}

	public function test_sets_badge_border_style_default_when_option_does_not_exist(): void {
		// Arrange.
		delete_option( OUTLET_BADGE_BORDER_STYLE_OPTION );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( 'none', get_option( OUTLET_BADGE_BORDER_STYLE_OPTION ) );
	}

	public function test_does_not_overwrite_existing_badge_border_style_option(): void {
		// Arrange.
		update_option( OUTLET_BADGE_BORDER_STYLE_OPTION, 'solid' );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( 'solid', get_option( OUTLET_BADGE_BORDER_STYLE_OPTION ) );
	}

	public function test_sets_badge_border_width_default_when_option_does_not_exist(): void {
		// Arrange.
		delete_option( OUTLET_BADGE_BORDER_WIDTH_OPTION );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( '0', get_option( OUTLET_BADGE_BORDER_WIDTH_OPTION ) );
	}

	public function test_does_not_overwrite_existing_badge_border_width_option(): void {
		// Arrange.
		update_option( OUTLET_BADGE_BORDER_WIDTH_OPTION, '2px' );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( '2px', get_option( OUTLET_BADGE_BORDER_WIDTH_OPTION ) );
	}

	public function test_does_not_overwrite_existing_badge_border_radius_option(): void {
		// Arrange.
		update_option( OUTLET_BADGE_BORDER_RADIUS_OPTION, '50%' );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( '50%', get_option( OUTLET_BADGE_BORDER_RADIUS_OPTION ) );
	}

	public function test_sets_badge_font_weight_default_when_option_does_not_exist(): void {
		// Arrange.
		delete_option( OUTLET_BADGE_FONT_WEIGHT_OPTION );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( '600', get_option( OUTLET_BADGE_FONT_WEIGHT_OPTION ) );
	}

	public function test_does_not_overwrite_existing_badge_font_weight_option(): void {
		// Arrange.
		update_option( OUTLET_BADGE_FONT_WEIGHT_OPTION, '400' );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( '400', get_option( OUTLET_BADGE_FONT_WEIGHT_OPTION ) );
	}

	public function test_sets_badge_scale_default_when_option_does_not_exist(): void {
		// Arrange.
		delete_option( OUTLET_BADGE_SCALE_OPTION );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( 166, get_option( OUTLET_BADGE_SCALE_OPTION ) );
	}

	public function test_does_not_overwrite_existing_badge_scale_option(): void {
		// Arrange.
		update_option( OUTLET_BADGE_SCALE_OPTION, 999 );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( 999, get_option( OUTLET_BADGE_SCALE_OPTION ) );
	}

	public function test_sets_badge_density_default_when_option_does_not_exist(): void {
		// Arrange.
		delete_option( OUTLET_BADGE_DENSITY_OPTION );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( 50, get_option( OUTLET_BADGE_DENSITY_OPTION ) );
	}

	public function test_does_not_overwrite_existing_badge_density_option(): void {
		// Arrange.
		update_option( OUTLET_BADGE_DENSITY_OPTION, 80 );

		// Act.
		seed_settings();

		// Assert.
		$this->assertSame( 80, get_option( OUTLET_BADGE_DENSITY_OPTION ) );
	}
}
