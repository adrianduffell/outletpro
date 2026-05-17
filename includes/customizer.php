<?php
/**
 * Customizer integration functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Default badge text colour (dark).
 *
 * @internal
 */
const CLEARANCE_BADGE_TEXT_COLOUR_DEFAULT = '#222';

/**
 * Default badge background colour (yellow).
 *
 * @internal
 */
const CLEARANCE_BADGE_BG_COLOUR_DEFAULT = '#FFEE85';

/**
 * Helper to initialize customizer integration.
 *
 * @internal
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
		CLEARANCE_BADGE_LABEL_OPTION,
		array(
			'type'              => 'option',
			'default'           => __( 'Clearance', 'wc-clearance' ),
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
		CLEARANCE_BADGE_BG_COLOR_OPTION,
		array(
			'type'              => 'option',
			'default'           => CLEARANCE_BADGE_BG_COLOUR_DEFAULT,
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);

	$wp_customize->add_control(
		new \WP_Customize_Color_Control(
			$wp_customize,
			CLEARANCE_BADGE_BG_COLOR_OPTION,
			array(
				'label'   => __( 'Badge background color', 'wc-clearance' ),
				'section' => 'wc_clearance',
			)
		)
	);

	$wp_customize->add_setting(
		CLEARANCE_BADGE_TEXT_COLOR_OPTION,
		array(
			'type'              => 'option',
			'default'           => CLEARANCE_BADGE_TEXT_COLOUR_DEFAULT,
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);

	$wp_customize->add_control(
		new \WP_Customize_Color_Control(
			$wp_customize,
			CLEARANCE_BADGE_TEXT_COLOR_OPTION,
			array(
				'label'   => __( 'Badge text color', 'wc-clearance' ),
				'section' => 'wc_clearance',
			)
		)
	);

	$wp_customize->add_setting(
		CLEARANCE_MESSAGE_OPTION,
		array(
			'type'              => 'option',
			'default'           => '',
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
		CLEARANCE_BADGE_SCALE_OPTION,
		array(
			'type'              => 'option',
			'default'           => 166,
			'sanitize_callback' => 'absint',
		)
	);

	$wp_customize->add_control(
		CLEARANCE_BADGE_SCALE_OPTION,
		array(
			'label'   => __( 'Badge scale', 'wc-clearance' ),
			'section' => 'wc_clearance',
			'type'    => 'select',
			'choices' => array(
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
		)
	);
}
