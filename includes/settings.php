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
 * WordPress option key used to store the badge border color.
 *
 * @internal
 */
const CLEARANCE_BADGE_BORDER_COLOR_OPTION = 'wc_clearance_badge_border_color';

/**
 * WordPress option key used to store the badge border style.
 *
 * @internal
 */
const CLEARANCE_BADGE_BORDER_STYLE_OPTION = 'wc_clearance_badge_border_style';

/**
 * WordPress option key used to store the badge border width.
 *
 * @internal
 */
const CLEARANCE_BADGE_BORDER_WIDTH_OPTION = 'wc_clearance_badge_border_width';

/**
 * WordPress option key used to store the badge font size.
 *
 * @internal
 */
const CLEARANCE_BADGE_FONT_SIZE_OPTION = 'wc_clearance_badge_font_size';

/**
 * WordPress option key used to store the badge font weight.
 *
 * @internal
 */
const CLEARANCE_BADGE_FONT_WEIGHT_OPTION = 'wc_clearance_badge_font_weight';

/**
 * WordPress option key used to store the badge top padding.
 *
 * @internal
 */
const CLEARANCE_BADGE_PADDING_TOP_OPTION = 'wc_clearance_badge_padding_top';

/**
 * WordPress option key used to store the badge right padding.
 *
 * @internal
 */
const CLEARANCE_BADGE_PADDING_RIGHT_OPTION = 'wc_clearance_badge_padding_right';

/**
 * WordPress option key used to store the badge bottom padding.
 *
 * @internal
 */
const CLEARANCE_BADGE_PADDING_BOTTOM_OPTION = 'wc_clearance_badge_padding_bottom';

/**
 * WordPress option key used to store the badge left padding.
 *
 * @internal
 */
const CLEARANCE_BADGE_PADDING_LEFT_OPTION = 'wc_clearance_badge_padding_left';

/**
 * WordPress option key used to store the badge scale.
 *
 * @internal
 */
const CLEARANCE_BADGE_SCALE_OPTION = 'wc_clearance_badge_scale';

/**
 * Default clearance badge scale percentage.
 *
 * @internal
 */
const CLEARANCE_BADGE_SCALE_DEFAULT = 120;

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
	register_clearance_badge_border_color_setting();
	register_clearance_badge_border_style_setting();
	register_clearance_badge_border_width_setting();
	register_clearance_badge_border_radius_setting();
	register_clearance_badge_font_size_setting();
	register_clearance_badge_font_weight_setting();
	register_clearance_badge_padding_top_setting();
	register_clearance_badge_padding_right_setting();
	register_clearance_badge_padding_bottom_setting();
	register_clearance_badge_padding_left_setting();
	register_clearance_badge_scale_setting();
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
	add_option( CLEARANCE_BADGE_BORDER_COLOR_OPTION, '' );
	add_option( CLEARANCE_BADGE_BORDER_STYLE_OPTION, 'none' );
	add_option( CLEARANCE_BADGE_BORDER_WIDTH_OPTION, '0' );
	add_option( CLEARANCE_BADGE_BORDER_RADIUS_OPTION, '2px' );
	add_option( CLEARANCE_BADGE_FONT_SIZE_OPTION, '0.83em' );
	add_option( CLEARANCE_BADGE_FONT_WEIGHT_OPTION, '600' );
	add_option( CLEARANCE_BADGE_PADDING_TOP_OPTION, '0.36em' );
	add_option( CLEARANCE_BADGE_PADDING_RIGHT_OPTION, '0.36em' );
	add_option( CLEARANCE_BADGE_PADDING_BOTTOM_OPTION, '0.36em' );
	add_option( CLEARANCE_BADGE_PADDING_LEFT_OPTION, '0.36em' );
	add_option( CLEARANCE_BADGE_SCALE_OPTION, CLEARANCE_BADGE_SCALE_DEFAULT );
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
 * Register the clearance badge border color setting.
 *
 * @internal
 */
function register_clearance_badge_border_color_setting(): void {
	register_setting(
		'wc_clearance',
		CLEARANCE_BADGE_BORDER_COLOR_OPTION,
		array(
			'type'              => 'string',
			'label'             => __( 'Clearance badge border color', 'wc-clearance' ),
			'description'       => __( 'Store-wide clearance badge border color.', 'wc-clearance' ),
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
 * Register the clearance badge border style setting.
 *
 * @internal
 */
function register_clearance_badge_border_style_setting(): void {
	register_setting(
		'wc_clearance',
		CLEARANCE_BADGE_BORDER_STYLE_OPTION,
		array(
			'type'              => 'string',
			'label'             => __( 'Clearance badge border style', 'wc-clearance' ),
			'description'       => __( 'Store-wide clearance badge border style.', 'wc-clearance' ),
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
 * Register the clearance badge border width setting.
 *
 * @internal
 */
function register_clearance_badge_border_width_setting(): void {
	register_setting(
		'wc_clearance',
		CLEARANCE_BADGE_BORDER_WIDTH_OPTION,
		array(
			'type'              => 'string',
			'label'             => __( 'Clearance badge border width', 'wc-clearance' ),
			'description'       => __( 'Store-wide clearance badge border width.', 'wc-clearance' ),
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
 * Register the clearance badge font weight setting.
 *
 * @internal
 */
function register_clearance_badge_font_weight_setting(): void {
	register_setting(
		'wc_clearance',
		CLEARANCE_BADGE_FONT_WEIGHT_OPTION,
		array(
			'type'              => 'string',
			'label'             => __( 'Clearance badge font weight', 'wc-clearance' ),
			'description'       => __( 'Store-wide clearance badge font weight.', 'wc-clearance' ),
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
 * Register the clearance badge top padding setting.
 *
 * @internal
 */
function register_clearance_badge_padding_top_setting(): void {
	register_setting(
		'wc_clearance',
		CLEARANCE_BADGE_PADDING_TOP_OPTION,
		array(
			'type'              => 'string',
			'label'             => __( 'Clearance badge top padding', 'wc-clearance' ),
			'description'       => __( 'Store-wide clearance badge top padding.', 'wc-clearance' ),
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
 * Register the clearance badge right padding setting.
 *
 * @internal
 */
function register_clearance_badge_padding_right_setting(): void {
	register_setting(
		'wc_clearance',
		CLEARANCE_BADGE_PADDING_RIGHT_OPTION,
		array(
			'type'              => 'string',
			'label'             => __( 'Clearance badge right padding', 'wc-clearance' ),
			'description'       => __( 'Store-wide clearance badge right padding.', 'wc-clearance' ),
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
 * Register the clearance badge bottom padding setting.
 *
 * @internal
 */
function register_clearance_badge_padding_bottom_setting(): void {
	register_setting(
		'wc_clearance',
		CLEARANCE_BADGE_PADDING_BOTTOM_OPTION,
		array(
			'type'              => 'string',
			'label'             => __( 'Clearance badge bottom padding', 'wc-clearance' ),
			'description'       => __( 'Store-wide clearance badge bottom padding.', 'wc-clearance' ),
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
 * Register the clearance badge left padding setting.
 *
 * @internal
 */
function register_clearance_badge_padding_left_setting(): void {
	register_setting(
		'wc_clearance',
		CLEARANCE_BADGE_PADDING_LEFT_OPTION,
		array(
			'type'              => 'string',
			'label'             => __( 'Clearance badge left padding', 'wc-clearance' ),
			'description'       => __( 'Store-wide clearance badge left padding.', 'wc-clearance' ),
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
 * Register the clearance badge scale setting.
 *
 * @internal
 */
function register_clearance_badge_scale_setting(): void {
	register_setting(
		'wc_clearance',
		CLEARANCE_BADGE_SCALE_OPTION,
		array(
			'type'              => 'integer',
			'label'             => __( 'Clearance badge scale', 'wc-clearance' ),
			'description'       => __( 'Percentage size of the clearance badge relative to the surrounding text cap-height.', 'wc-clearance' ),
			'default'           => CLEARANCE_BADGE_SCALE_DEFAULT,
			'sanitize_callback' => 'absint',
			'show_in_rest'      => array(
				'schema' => array(
					'type'    => 'integer',
					'minimum' => 0,
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
