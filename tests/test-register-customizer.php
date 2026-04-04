<?php
/**
 * Tests for the customizer integration functions.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\init_customizer;
use function WC_Clearance\register_customizer_hook;
use const WC_Clearance\CLEARANCE_BADGE_BG_COLOUR_DEFAULT;
use const WC_Clearance\CLEARANCE_BADGE_BG_COLOUR_MOD;
use const WC_Clearance\CLEARANCE_BADGE_LABEL_OPTION;
use const WC_Clearance\CLEARANCE_BADGE_TEXT_COLOUR_DEFAULT;
use const WC_Clearance\CLEARANCE_BADGE_TEXT_COLOUR_MOD;
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
		$this->assertSame( 'Not eligible for change of mind returns', $wp_customize->get_setting( CLEARANCE_MESSAGE_OPTION )->default );
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
		$this->assertSame( 'Clearance', $wp_customize->get_setting( CLEARANCE_BADGE_LABEL_OPTION )->default );
	}
}
