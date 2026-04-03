<?php
/**
 * Tests for the customizer integration functions.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\get_badge_bg_colour;
use function WC_Clearance\get_badge_label;
use function WC_Clearance\get_badge_text_colour;
use function WC_Clearance\get_clearance_message;
use function WC_Clearance\init_customizer;
use function WC_Clearance\register_customizer_hook;
use const WC_Clearance\CLEARANCE_BADGE_BG_COLOUR_DEFAULT;
use const WC_Clearance\CLEARANCE_BADGE_BG_COLOUR_MOD;
use const WC_Clearance\CLEARANCE_BADGE_LABEL_DEFAULT;
use const WC_Clearance\CLEARANCE_BADGE_LABEL_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_TEXT_COLOUR_DEFAULT;
use const WC_Clearance\CLEARANCE_BADGE_TEXT_COLOUR_MOD;
use const WC_Clearance\CLEARANCE_MESSAGE_DEFAULT;
use const WC_Clearance\CLEARANCE_MESSAGE_OPTION;

class Test_Register_Customizer extends WP_UnitTestCase {

	public function test_registers_wc_clearance_section(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertNotNull( $wp_customize->get_section( 'wc_clearance' ) );
	}

	public function test_section_is_nested_in_woocommerce_panel(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertSame( 'woocommerce', $wp_customize->get_section( 'wc_clearance' )->panel );
	}

	public function test_registers_message_setting(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertNotNull( $wp_customize->get_setting( CLEARANCE_MESSAGE_OPTION ) );
	}

	public function test_message_setting_type_is_option(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertSame( 'option', $wp_customize->get_setting( CLEARANCE_MESSAGE_OPTION )->type );
	}

	public function test_message_setting_default(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertSame( CLEARANCE_MESSAGE_DEFAULT, $wp_customize->get_setting( CLEARANCE_MESSAGE_OPTION )->default );
	}

	public function test_registers_badge_text_colour_setting(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertNotNull( $wp_customize->get_setting( CLEARANCE_BADGE_TEXT_COLOUR_MOD ) );
	}

	public function test_badge_text_colour_setting_type_is_theme_mod(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertSame( 'theme_mod', $wp_customize->get_setting( CLEARANCE_BADGE_TEXT_COLOUR_MOD )->type );
	}

	public function test_badge_text_colour_setting_default(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertSame( CLEARANCE_BADGE_TEXT_COLOUR_DEFAULT, $wp_customize->get_setting( CLEARANCE_BADGE_TEXT_COLOUR_MOD )->default );
	}

	public function test_registers_badge_bg_colour_setting(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertNotNull( $wp_customize->get_setting( CLEARANCE_BADGE_BG_COLOUR_MOD ) );
	}

	public function test_badge_bg_colour_setting_type_is_theme_mod(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertSame( 'theme_mod', $wp_customize->get_setting( CLEARANCE_BADGE_BG_COLOUR_MOD )->type );
	}

	public function test_badge_bg_colour_setting_default(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertSame( CLEARANCE_BADGE_BG_COLOUR_DEFAULT, $wp_customize->get_setting( CLEARANCE_BADGE_BG_COLOUR_MOD )->default );
	}

	public function test_init_customizer_hooks_into_customize_register(): void {
		// Arrange.
		remove_all_actions( 'customize_register' );

		// Act.
		init_customizer();

		// Assert.
		$this->assertSame( 10, has_action( 'customize_register', 'WC_Clearance\register_customizer_hook' ) );
	}

	public function test_get_clearance_message_returns_default_when_option_not_set(): void {
		// Arrange.
		delete_option( CLEARANCE_MESSAGE_OPTION );

		// Act.
		$result = get_clearance_message();

		// Assert.
		$this->assertSame( CLEARANCE_MESSAGE_DEFAULT, $result );
	}

	public function test_get_clearance_message_returns_stored_value(): void {
		// Arrange.
		update_option( CLEARANCE_MESSAGE_OPTION, 'No returns on sale items.' );

		// Act.
		$result = get_clearance_message();

		// Assert.
		$this->assertSame( 'No returns on sale items.', $result );
	}

	public function test_get_badge_text_colour_returns_default_when_mod_not_set(): void {
		// Arrange.
		remove_theme_mod( CLEARANCE_BADGE_TEXT_COLOUR_MOD );

		// Act.
		$result = get_badge_text_colour();

		// Assert.
		$this->assertSame( CLEARANCE_BADGE_TEXT_COLOUR_DEFAULT, $result );
	}

	public function test_get_badge_text_colour_returns_stored_value(): void {
		// Arrange.
		set_theme_mod( CLEARANCE_BADGE_TEXT_COLOUR_MOD, '#ff0000' );

		// Act.
		$result = get_badge_text_colour();

		// Assert.
		$this->assertSame( '#ff0000', $result );
	}

	public function test_get_badge_bg_colour_returns_default_when_mod_not_set(): void {
		// Arrange.
		remove_theme_mod( CLEARANCE_BADGE_BG_COLOUR_MOD );

		// Act.
		$result = get_badge_bg_colour();

		// Assert.
		$this->assertSame( CLEARANCE_BADGE_BG_COLOUR_DEFAULT, $result );
	}

	public function test_get_badge_bg_colour_returns_stored_value(): void {
		// Arrange.
		set_theme_mod( CLEARANCE_BADGE_BG_COLOUR_MOD, '#123456' );

		// Act.
		$result = get_badge_bg_colour();

		// Assert.
		$this->assertSame( '#123456', $result );
	}

	public function test_registers_badge_label_setting(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertNotNull( $wp_customize->get_setting( CLEARANCE_BADGE_LABEL_OPTION ) );
	}

	public function test_badge_label_setting_type_is_option(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertSame( 'option', $wp_customize->get_setting( CLEARANCE_BADGE_LABEL_OPTION )->type );
	}

	public function test_badge_label_setting_default(): void {
		// Arrange.
		$wp_customize = new WP_Customize_Manager();

		// Act.
		register_customizer_hook( $wp_customize );

		// Assert.
		$this->assertSame( CLEARANCE_BADGE_LABEL_DEFAULT, $wp_customize->get_setting( CLEARANCE_BADGE_LABEL_OPTION )->default );
	}

	public function test_get_badge_label_returns_default_when_option_not_set(): void {
		// Arrange.
		delete_option( CLEARANCE_BADGE_LABEL_OPTION );

		// Act.
		$result = get_badge_label();

		// Assert.
		$this->assertSame( CLEARANCE_BADGE_LABEL_DEFAULT, $result );
	}

	public function test_get_badge_label_returns_stored_value(): void {
		// Arrange.
		update_option( CLEARANCE_BADGE_LABEL_OPTION, 'On Sale' );

		// Act.
		$result = get_badge_label();

		// Assert.
		$this->assertSame( 'On Sale', $result );
	}
}
