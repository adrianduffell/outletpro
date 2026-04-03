<?php
/**
 * Customizer integration functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * WordPress option key used to store the clearance message.
 *
 * @since 1.0.0
 */
const CLEARANCE_MESSAGE_OPTION = 'wc_clearance_message';

/**
 * WordPress option key used to store the badge label text.
 *
 * @since 1.0.0
 */
const CLEARANCE_BADGE_LABEL_OPTION = 'wc_clearance_badge_label';

/**
 * Theme mod key used to store the badge text colour.
 *
 * @since 1.0.0
 */
const CLEARANCE_BADGE_TEXT_COLOUR_MOD = 'wc_clearance_badge_text_colour';

/**
 * Theme mod key used to store the badge background colour.
 *
 * @since 1.0.0
 */
const CLEARANCE_BADGE_BG_COLOUR_MOD = 'wc_clearance_badge_bg_colour';

/**
 * Default clearance message.
 *
 * @since 1.0.0
 */
const CLEARANCE_MESSAGE_DEFAULT = 'Not eligible for change of mind returns.';

/**
 * Default badge label text.
 *
 * @since 1.0.0
 */
const CLEARANCE_BADGE_LABEL_DEFAULT = 'Clearance';

/**
 * Default badge text colour (dark).
 *
 * @since 1.0.0
 */
const CLEARANCE_BADGE_TEXT_COLOUR_DEFAULT = '#222';

/**
 * Default badge background colour (yellow).
 *
 * @since 1.0.0
 */
const CLEARANCE_BADGE_BG_COLOUR_DEFAULT = '#FFEE85';

/**
 * Helper to initialize customizer integration.
 *
 * @since 1.0.0
 */
function init_customizer(): void {
	add_action( 'customize_register', 'WC_Clearance\register_customizer_hook' );
}

/**
 * Register the clearance customizer section, settings and controls.
 *
 * @param \WP_Customize_Manager $wp_customize Customizer manager instance.
 * @internal WordPress action hook
 */
function register_customizer_hook( \WP_Customize_Manager $wp_customize ): void {
	$wp_customize->add_section(
		'wc_clearance',
		array(
			'title' => __( 'Clearance Section', 'wc-clearance' ),
			'panel' => 'woocommerce',
		)
	);

	$wp_customize->add_setting(
		CLEARANCE_MESSAGE_OPTION,
		array(
			'type'              => 'option',
			'default'           => CLEARANCE_MESSAGE_DEFAULT,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		CLEARANCE_MESSAGE_OPTION,
		array(
			'label'   => __( 'Message', 'wc-clearance' ),
			'section' => 'wc_clearance',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		CLEARANCE_BADGE_LABEL_OPTION,
		array(
			'type'              => 'option',
			'default'           => CLEARANCE_BADGE_LABEL_DEFAULT,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		CLEARANCE_BADGE_LABEL_OPTION,
		array(
			'label'   => __( 'Badge label', 'wc-clearance' ),
			'section' => 'wc_clearance',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		CLEARANCE_BADGE_TEXT_COLOUR_MOD,
		array(
			'default'           => CLEARANCE_BADGE_TEXT_COLOUR_DEFAULT,
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);

	$wp_customize->add_control(
		new \WP_Customize_Color_Control(
			$wp_customize,
			CLEARANCE_BADGE_TEXT_COLOUR_MOD,
			array(
				'label'   => __( 'Badge text colour', 'wc-clearance' ),
				'section' => 'wc_clearance',
			)
		)
	);

	$wp_customize->add_setting(
		CLEARANCE_BADGE_BG_COLOUR_MOD,
		array(
			'default'           => CLEARANCE_BADGE_BG_COLOUR_DEFAULT,
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);

	$wp_customize->add_control(
		new \WP_Customize_Color_Control(
			$wp_customize,
			CLEARANCE_BADGE_BG_COLOUR_MOD,
			array(
				'label'   => __( 'Badge background colour', 'wc-clearance' ),
				'section' => 'wc_clearance',
			)
		)
	);
}

/**
 * Get the clearance message.
 *
 * @since 1.0.0
 */
function get_clearance_message(): string {
	$value = get_option( CLEARANCE_MESSAGE_OPTION, CLEARANCE_MESSAGE_DEFAULT );
	return is_string( $value ) ? $value : CLEARANCE_MESSAGE_DEFAULT;
}

/**
 * Get the badge text colour.
 *
 * @since 1.0.0
 */
function get_badge_text_colour(): string {
	$value = get_theme_mod( CLEARANCE_BADGE_TEXT_COLOUR_MOD, CLEARANCE_BADGE_TEXT_COLOUR_DEFAULT );
	return is_string( $value ) ? $value : CLEARANCE_BADGE_TEXT_COLOUR_DEFAULT;
}

/**
 * Get the badge background colour.
 *
 * @since 1.0.0
 */
function get_badge_bg_colour(): string {
	$value = get_theme_mod( CLEARANCE_BADGE_BG_COLOUR_MOD, CLEARANCE_BADGE_BG_COLOUR_DEFAULT );
	return is_string( $value ) ? $value : CLEARANCE_BADGE_BG_COLOUR_DEFAULT;
}

/**
 * Get the badge label text.
 *
 * @since 1.0.0
 */
function get_badge_label(): string {
	$value = get_option( CLEARANCE_BADGE_LABEL_OPTION, CLEARANCE_BADGE_LABEL_DEFAULT );
	return is_string( $value ) ? $value : CLEARANCE_BADGE_LABEL_DEFAULT;
}
