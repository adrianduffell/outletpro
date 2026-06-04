<?php
/**
 * Customizer integration functions.
 *
 * @package WC_Outlet
 */

namespace WC_Outlet;

defined( 'ABSPATH' ) || exit;

/**
 * Default badge text colour (dark).
 *
 * @internal
 */
const OUTLET_BADGE_TEXT_COLOUR_DEFAULT = '#FFFFFF';

/**
 * Default badge background colour (yellow).
 *
 * @internal
 */
const OUTLET_BADGE_BG_COLOUR_DEFAULT = '#F81240';

/**
 * Helper to initialize customizer integration.
 *
 * @internal
 */
function init_customizer(): void {
	add_action( 'customize_register', 'WC_Outlet\register_customizer_hook' );
}

/**
 * Register the outlet customizer section, settings and controls.
 *
 * @param \WP_Customize_Manager $wp_customize Customizer manager instance.
 * @internal WordPress action hook
 */
function register_customizer_hook( \WP_Customize_Manager $wp_customize ): void {
	$wp_customize->add_section(
		'wc_outlet',
		array(
			'title' => __( 'Outlet', 'outletpro' ),
			'panel' => 'woocommerce',
		)
	);

	$wp_customize->add_setting(
		OUTLET_BADGE_LABEL_OPTION,
		array(
			'type'              => 'option',
			'default'           => __( 'Last chance', 'outletpro' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		OUTLET_BADGE_LABEL_OPTION,
		array(
			'label'   => __( 'Badge label', 'outletpro' ),
			'section' => 'wc_outlet',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		OUTLET_BADGE_BG_COLOR_OPTION,
		array(
			'type'              => 'option',
			'default'           => OUTLET_BADGE_BG_COLOUR_DEFAULT,
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);

	$wp_customize->add_control(
		new \WP_Customize_Color_Control(
			$wp_customize,
			OUTLET_BADGE_BG_COLOR_OPTION,
			array(
				'label'   => __( 'Badge background color', 'outletpro' ),
				'section' => 'wc_outlet',
			)
		)
	);

	$wp_customize->add_setting(
		OUTLET_BADGE_TEXT_COLOR_OPTION,
		array(
			'type'              => 'option',
			'default'           => OUTLET_BADGE_TEXT_COLOUR_DEFAULT,
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);

	$wp_customize->add_control(
		new \WP_Customize_Color_Control(
			$wp_customize,
			OUTLET_BADGE_TEXT_COLOR_OPTION,
			array(
				'label'   => __( 'Badge text color', 'outletpro' ),
				'section' => 'wc_outlet',
			)
		)
	);

	$wp_customize->add_setting(
		OUTLET_MESSAGE_OPTION,
		array(
			'type'              => 'option',
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		OUTLET_MESSAGE_OPTION,
		array(
			'label'   => __( 'Message', 'outletpro' ),
			'section' => 'wc_outlet',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		OUTLET_BADGE_SCALE_OPTION,
		array(
			'type'              => 'option',
			'default'           => 166,
			'sanitize_callback' => 'absint',
		)
	);

	$wp_customize->add_control(
		OUTLET_BADGE_SCALE_OPTION,
		array(
			'label'   => __( 'Badge scale', 'outletpro' ),
			'section' => 'wc_outlet',
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
