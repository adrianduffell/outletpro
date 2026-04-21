<?php
/**
 * Settings functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * WordPress option key used to store the clearance section page ID.
 *
 * @since 1.0.0
 */
const CLEARANCE_PAGE_OPTION = 'wc_clearance_page_id';

/**
 * WordPress option key used to store the badge label text.
 *
 * @since 1.0.0
 */
const CLEARANCE_BADGE_LABEL_OPTION = 'wc_clearance_badge_label';

/**
 * WordPress option key used to store the badge text color.
 *
 * @since 1.0.0
 */
const CLEARANCE_BADGE_TEXT_COLOR_OPTION = 'wc_clearance_badge_text_color';

/**
 * WordPress option key used to store the badge background color.
 *
 * @since 1.0.0
 */
const CLEARANCE_BADGE_BG_COLOR_OPTION = 'wc_clearance_badge_bg_color';

/**
 * Check whether the settings screen is enabled.
 *
 * @internal
 */
function settings_screen_enabled(): bool {
	return (bool) apply_filters( 'wc_clearance_settings_screen_enabled', false );
}

/**
 * Helper to initialize settings.
 *
 * @internal
 */
function init_settings(): void {
	register_clearance_page_setting();
	register_clearance_badge_label_setting();
	register_clearance_badge_text_color_setting();
	register_clearance_badge_bg_color_setting();
}

/**
 * Seed option values with defaults.
 *
 * Uses add_option() so that existing values are never overwritten. This
 * preserves the default at the time of installation even if defaults change
 * in future versions.
 *
 * @internal
 */
function seed_settings(): void {
	add_option( CLEARANCE_BADGE_LABEL_OPTION, __( 'Clearance', 'wc-clearance' ) );
}

/**
 * Register the clearance page ID setting.
 *
 * @since 1.0.0
 */
function register_clearance_page_setting(): void {
	register_setting(
		'wc_clearance',
		CLEARANCE_PAGE_OPTION,
		array(
			'type'         => 'integer',
			'show_in_rest' => array(
				'schema' => array(
					'type'    => 'integer',
					'minimum' => 1,
				),
			),
		)
	);
}

/**
 * Register the clearance badge label setting.
 *
 * @since 1.0.0
 */
function register_clearance_badge_label_setting(): void {
	register_setting(
		'wc_clearance',
		CLEARANCE_BADGE_LABEL_OPTION,
		array(
			'type'              => 'string',
			'label'             => __( 'Clearance badge label', 'wc-clearance' ),
			'description'       => __( 'Store-wide clearance badge label.', 'wc-clearance' ),
			'default'           => __( 'Clearance', 'wc-clearance' ),
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => array(
				'schema' => array(
					'type' => 'string',
				),
			),
		)
	);
}

/**
 * Register the clearance badge text color setting.
 *
 * @since 1.0.0
 */
function register_clearance_badge_text_color_setting(): void {
	register_setting(
		'wc_clearance',
		CLEARANCE_BADGE_TEXT_COLOR_OPTION,
		array(
			'type'              => 'string',
			'label'             => __( 'Clearance badge text color', 'wc-clearance' ),
			'description'       => __( 'Store-wide clearance badge text color.', 'wc-clearance' ),
			'default'           => '#222',
			'sanitize_callback' => 'sanitize_hex_color',
			'show_in_rest'      => array(
				'schema' => array(
					'type' => 'string',
				),
			),
		)
	);
}

/**
 * Register the clearance badge background color setting.
 *
 * @since 1.0.0
 */
function register_clearance_badge_bg_color_setting(): void {
	register_setting(
		'wc_clearance',
		CLEARANCE_BADGE_BG_COLOR_OPTION,
		array(
			'type'              => 'string',
			'label'             => __( 'Clearance badge background color', 'wc-clearance' ),
			'description'       => __( 'Store-wide clearance badge background color.', 'wc-clearance' ),
			'default'           => '#FFEE85',
			'sanitize_callback' => 'sanitize_hex_color',
			'show_in_rest'      => array(
				'schema' => array(
					'type' => 'string',
				),
			),
		)
	);
}

/**
 * Get the clearance section page ID from the option.
 *
 * Validates the page ID is a positive integer. Zero and non-digit values
 * indicate a corrupted state and exceptions are thrown in these cases.
 *
 * Returns the page ID as a normalized int, or null when the option does
 * not exist.
 *
 * @since 1.0.0
 * @throws \UnexpectedValueException If the stored option value is not an integer greater than zero.
 */
function get_clearance_page_id(): ?int {
	$value = get_option( CLEARANCE_PAGE_OPTION );

	if ( false === $value ) {
		return null;
	}

	if ( ! is_scalar( $value ) ) {
		throw new \UnexpectedValueException( 'Invalid clearance page option value.' );
	}

	// Cast the value to a string for simpler validation.
	// The original value may have been returned as an int or a string depending on the storage and caching layer.
	$as_string = (string) $value;

	// Validate the value is a positive integer.
	if ( ! ctype_digit( $as_string ) || '0' === $as_string ) {
		throw new \UnexpectedValueException( 'Invalid clearance page option value.' );
	}

	// Return the original value in normalized form.
	return (int) $value;
}
