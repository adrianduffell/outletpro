<?php
/**
 * Tests for the customizer integration functions.
 *
 * @package OutletPro
 */

use function OutletPro\register_customizer_hook;
use const OutletPro\OUTLET_BADGE_BG_COLOR_OPTION;
use const OutletPro\OUTLET_BADGE_BG_COLOUR_DEFAULT;
use const OutletPro\OUTLET_BADGE_LABEL_OPTION;
use const OutletPro\OUTLET_BADGE_SCALE_OPTION;
use const OutletPro\OUTLET_BADGE_TEXT_COLOR_OPTION;
use const OutletPro\OUTLET_BADGE_TEXT_COLOUR_DEFAULT;
use const OutletPro\OUTLET_MESSAGE_OPTION;

class Test_Register_Customizer extends WP_UnitTestCase {

	public function test_registers_outletpro_section(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertNotNull( $wp_customize->get_section( 'outletpro' ) );
	}

	public function test_section_is_nested_in_woocommerce_panel(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertSame( 'woocommerce', $wp_customize->get_section( 'outletpro' )->panel );
	}

	public function test_registers_message_setting(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertNotNull( $wp_customize->get_setting( OUTLET_MESSAGE_OPTION ) );
	}

	public function test_message_setting_type_is_option(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertSame( 'option', $wp_customize->get_setting( OUTLET_MESSAGE_OPTION )->type );
	}

	public function test_message_setting_default(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertSame( '', $wp_customize->get_setting( OUTLET_MESSAGE_OPTION )->default );
	}

	public function test_registers_badge_text_colour_setting(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertNotNull( $wp_customize->get_setting( OUTLET_BADGE_TEXT_COLOR_OPTION ) );
	}

	public function test_badge_text_colour_setting_type_is_option(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertSame( 'option', $wp_customize->get_setting( OUTLET_BADGE_TEXT_COLOR_OPTION )->type );
	}

	public function test_badge_text_colour_setting_default(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertSame( OUTLET_BADGE_TEXT_COLOUR_DEFAULT, $wp_customize->get_setting( OUTLET_BADGE_TEXT_COLOR_OPTION )->default );
	}

	public function test_registers_badge_bg_colour_setting(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertNotNull( $wp_customize->get_setting( OUTLET_BADGE_BG_COLOR_OPTION ) );
	}

	public function test_badge_bg_colour_setting_type_is_option(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertSame( 'option', $wp_customize->get_setting( OUTLET_BADGE_BG_COLOR_OPTION )->type );
	}

	public function test_badge_bg_colour_setting_default(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertSame( OUTLET_BADGE_BG_COLOUR_DEFAULT, $wp_customize->get_setting( OUTLET_BADGE_BG_COLOR_OPTION )->default );
	}

	public function test_registers_badge_label_setting(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertNotNull( $wp_customize->get_setting( OUTLET_BADGE_LABEL_OPTION ) );
	}

	public function test_badge_label_setting_type_is_option(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertSame( 'option', $wp_customize->get_setting( OUTLET_BADGE_LABEL_OPTION )->type );
	}

	public function test_badge_label_setting_default(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertSame( 'Last chance', $wp_customize->get_setting( OUTLET_BADGE_LABEL_OPTION )->default );
	}

	public function test_registers_badge_scale_setting(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertNotNull( $wp_customize->get_setting( OUTLET_BADGE_SCALE_OPTION ) );
	}

	public function test_badge_scale_setting_type_is_option(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertSame( 'option', $wp_customize->get_setting( OUTLET_BADGE_SCALE_OPTION )->type );
	}

	public function test_badge_scale_setting_default(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertSame( 166, $wp_customize->get_setting( OUTLET_BADGE_SCALE_OPTION )->default );
	}

	public function test_registers_badge_scale_select_control_with_expected_choices(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$control = $wp_customize->get_control( OUTLET_BADGE_SCALE_OPTION );

		$this->assertNotNull( $control );
		$this->assertSame( 'Badge scale', $control->label );
		$this->assertSame( 'select', $control->type );
		$this->assertSame(
			array(
				100 => '1.00x',
				125 => '1.25x',
				133 => '1.33x',
				150 => '1.50x',
				166 => '1.66x',
				170 => '1.70x',
				180 => '1.80x',
				190 => '1.90x',
				200 => '2.00x',
			),
			$control->choices
		);
	}
}
