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
 * Helper to initialize settings.
 *
 * @since 1.0.0
 */
function init_settings(): void {
	register_clearance_page_setting();
	register_clearance_badge_label_setting();
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
			'label'             => 'Clearance badge label',
			'description'       => 'Store-wide clearance badge label.',
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
