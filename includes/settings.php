<?php
/**
 * Settings functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * WordPress option key used to store the clearance message.
 *
 * @internal
 */
const CLEARANCE_MESSAGE_OPTION = 'wc_clearance_message';

/**
 * WordPress option key used to store the clearance section page ID.
 *
 * @internal
 */
const CLEARANCE_PAGE_OPTION = 'wc_clearance_page_id';

/**
 * WordPress option key used to store the badge label text.
 *
 * @internal
 */
const CLEARANCE_BADGE_LABEL_OPTION = 'wc_clearance_badge_label';

/**
 * WordPress option key used to store the badge text color.
 *
 * @internal
 */
const CLEARANCE_BADGE_TEXT_COLOR_OPTION = 'wc_clearance_badge_text_color';

/**
 * WordPress option key used to store the badge background color.
 *
 * @internal
 */
const CLEARANCE_BADGE_BG_COLOR_OPTION = 'wc_clearance_badge_bg_color';

/**
 * WordPress option key used to store the badge border radius.
 *
 * @internal
 */
const CLEARANCE_BADGE_BORDER_RADIUS_OPTION = 'wc_clearance_badge_border_radius';

/**
 * WordPress option key used to store the badge font size.
 *
 * @internal
 */
const CLEARANCE_BADGE_FONT_SIZE_OPTION = 'wc_clearance_badge_font_size';

/**
 * Sanitize a CSS property value, rejecting values that contain CSS block delimiters or
 * values that fail sanitize_text_field().
 *
 * Intentionally light-weight as CSS is broad and evolving.
 *
 * @internal
 *
 * @param mixed $value The CSS property value to sanitize.
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
 */
function sanitize_css_value( $value ): string {
	if ( ! is_string( $value ) ) {
		return '';
	}

	$value = sanitize_text_field( $value );

	if (
		false !== strpos( $value, ';' ) ||
		false !== strpos( $value, '{' ) ||
		false !== strpos( $value, '}' )
	) {
		return '';
	}

	return $value;
}

/**
 * Sanitize a border object from Gutenberg's BorderControl component format.
 *
 * @internal
 *
 * @param mixed $value The border value to sanitize.
 * @return array{
 *     color: string,
 *     style: string,
 *     width: string
 * }
 * @phpcsSuppress SlevomatCodingStandard.TypeHints
 */
function sanitize_border( $value ): array {
	if ( ! is_array( $value ) ) {
		return array();
	}

	$color = $value['color'] ?? '';
	$style = $value['style'] ?? '';
	$width = $value['width'] ?? '';

	return array(
		'color' => sanitize_css_value( is_scalar( $color ) ? strval( $color ) : '' ),
		'style' => sanitize_css_value( is_scalar( $style ) ? strval( $style ) : '' ),
		'width' => sanitize_css_value( is_scalar( $width ) ? strval( $width ) : '' ),
	);
}

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
	register_clearance_badge_border_radius_setting();
	register_clearance_badge_font_size_setting();
	register_clearance_message_setting();
}

/**
 * Get the default clearance message based on the store's country.
 *
 * Returns "Only while supplies last" for US and Canada, and
 * "Only while stocks last" for all other countries.
 *
 * @since 1.0.0
 */
function get_default_clearance_message(): string {
	$base_location = (string) get_option( 'woocommerce_default_country', '' );
	$country       = explode( ':', $base_location )[0];

	if ( in_array( $country, array( 'US', 'CA' ), true ) ) {
		return __( 'Only while supplies last', 'wc-clearance' );
	}

	return __( 'Only while stocks last', 'wc-clearance' );
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
	add_option( CLEARANCE_BADGE_TEXT_COLOR_OPTION, '#222' );
	add_option( CLEARANCE_BADGE_BG_COLOR_OPTION, '#FFEE85' );
	add_option( CLEARANCE_BADGE_BORDER_RADIUS_OPTION, '2px' );
	add_option( CLEARANCE_BADGE_FONT_SIZE_OPTION, '0.875rem' );
	add_option( CLEARANCE_MESSAGE_OPTION, get_default_clearance_message() );
}

/**
 * Register the clearance page ID setting.
 *
 * @internal
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
 * @internal
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
 * Register the clearance badge border radius setting.
 *
 * @internal
 */
function register_clearance_badge_border_radius_setting(): void {
	register_setting(
		'wc_clearance',
		CLEARANCE_BADGE_BORDER_RADIUS_OPTION,
		array(
			'type'              => 'string',
			'label'             => __( 'Clearance badge border radius', 'wc-clearance' ),
			'description'       => __( 'Store-wide clearance badge border radius.', 'wc-clearance' ),
			'default'           => '',
			'sanitize_callback' => 'WC_Clearance\sanitize_css_value',
			'show_in_rest'      => array(
				'schema' => array(
					'type' => 'string',
				),
			),
		)
	);
}

/**
 * Register the clearance badge font size setting.
 *
 * @internal
 */
function register_clearance_badge_font_size_setting(): void {
	register_setting(
		'wc_clearance',
		CLEARANCE_BADGE_FONT_SIZE_OPTION,
		array(
			'type'              => 'string',
			'label'             => __( 'Clearance badge font size', 'wc-clearance' ),
			'description'       => __( 'Store-wide clearance badge font size.', 'wc-clearance' ),
			'default'           => '',
			'sanitize_callback' => 'WC_Clearance\wc_clearance_sanitize_css_value',
			'show_in_rest'      => array(
				'schema' => array(
					'type' => 'string',
				),
			),
		)
	);
}

/**
 * Register the clearance message setting.
 *
 * @internal
 */
function register_clearance_message_setting(): void {
	register_setting(
		'wc_clearance',
		CLEARANCE_MESSAGE_OPTION,
		array(
			'type'              => 'string',
			'label'             => __( 'Clearance message', 'wc-clearance' ),
			'description'       => __( 'Message displayed on clearance products.', 'wc-clearance' ),
			'default'           => '',
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
