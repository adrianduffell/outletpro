<?php
/**
 * License settings functions.
 *
 * @package OutletPro
 * @subpackage License
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * WordPress option group used to store the license key.
 *
 * @internal
 */
const LICENSE_OPTIONS_GROUP = 'outletpro_license';

/**
 * WordPress option key used to store the license key.
 *
 * @internal
 */
const LICENSE_KEY_OPTION = 'outletpro_license_key';

/**
 * WordPress transient key used to cache license status.
 *
 * @internal
 * @see get_license_status()
 */
const LICENSE_STATUS_TRANSIENT = 'outletpro_license_status';

/**
 * Helper to initialize license settings.
 *
 * @internal
 */
function init_license_settings(): void {
	register_license_key_setting();

	add_action( 'add_option_' . LICENSE_KEY_OPTION, 'OutletPro\invalidate_license_cache_hook', 10, 0 );
	add_action( 'update_option_' . LICENSE_KEY_OPTION, 'OutletPro\invalidate_license_cache_hook', 10, 0 );
	add_action( 'delete_option_' . LICENSE_KEY_OPTION, 'OutletPro\invalidate_license_cache_hook', 10, 0 );
	add_action( 'add_option_' . LICENSE_KEY_OPTION, 'OutletPro\add_license_activation_hook', 10, 2 );
	add_action( 'update_option_' . LICENSE_KEY_OPTION, 'OutletPro\update_license_activation_hook', 10, 2 );
	add_action( 'delete_option', 'OutletPro\delete_license_activation_hook' );
}

/**
 * Helper to deinitialize license settings.
 *
 * @internal
 */
function deinit_license_settings(): void {
	unregister_setting( LICENSE_OPTIONS_GROUP, LICENSE_KEY_OPTION );
	remove_action( 'add_option_' . LICENSE_KEY_OPTION, 'OutletPro\invalidate_license_cache_hook', 10, 0 );
	remove_action( 'update_option_' . LICENSE_KEY_OPTION, 'OutletPro\invalidate_license_cache_hook', 10, 0 );
	remove_action( 'delete_option_' . LICENSE_KEY_OPTION, 'OutletPro\invalidate_license_cache_hook', 10, 0 );
	remove_action( 'add_option_' . LICENSE_KEY_OPTION, 'OutletPro\add_license_activation_hook', 10, 2 );
	remove_action( 'update_option_' . LICENSE_KEY_OPTION, 'OutletPro\update_license_activation_hook', 10, 2 );
	remove_action( 'delete_option', 'OutletPro\delete_license_activation_hook' );
}

/**
 * Activate a license key when the license key option is first added.
 *
 * Fired by `add_option_{LICENSE_KEY_OPTION}`.
 *
 * @param string $option The option name.
 * @param mixed  $license_key The added license key.
 * @internal WordPress action hook
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
 */
function add_license_activation_hook( string $option, $license_key ): void {
	try {
		activate_license( $license_key );
	} catch ( \RuntimeException $e ) {
		\wc_get_logger()->error( 'New license could not be activated.' );
	}
}

/**
 * Update the site activation when the license key option is changed.
 *
 * Fired by `update_option_{LICENSE_KEY_OPTION}`.
 *
 * @param mixed $previous_license_key The previous license key.
 * @param mixed $license_key The new license key.
 * @internal WordPress action hook
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
 */
function update_license_activation_hook( $previous_license_key, $license_key ): void {
	try {
		deactivate_license( $previous_license_key );
	} catch ( \RuntimeException $e ) {
		\wc_get_logger()->error( 'Previous license could not be deactivated.' );
	}

	try {
		activate_license( $license_key );
	} catch ( \RuntimeException $e ) {
		\wc_get_logger()->error( 'New license could not be activated.' );
	}
}

/**
 * Deactivate the license before the license key option is deleted.
 *
 * Fired by `delete_option`.
 *
 * @param string $option The option name.
 * @internal WordPress action hook
 */
function delete_license_activation_hook( string $option ): void {
	if ( LICENSE_KEY_OPTION !== $option ) {
		return;
	}

	$license_key = get_option( LICENSE_KEY_OPTION );

	try {
		deactivate_license( $license_key );
	} catch ( \RuntimeException $e ) {
		\wc_get_logger()->error( 'Previous license could not be deactivated.' );
	}
}


/**
 * Register the license key setting.
 *
 * @internal
 */
function register_license_key_setting(): void {
	register_setting(
		LICENSE_OPTIONS_GROUP,
		LICENSE_KEY_OPTION,
		array(
			'type'              => 'string',
			'label'             => __( 'License Key', 'outletpro' ),
			'description'       => __( 'Outlet Pro license key.', 'outletpro' ),
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
 * Invalidate the license cache when the license key option is added, updated, or deleted.
 *
 * Fired by `add_option_{LICENSE_KEY_OPTION}`, `update_option_{LICENSE_KEY_OPTION}`, and `delete_option_{LICENSE_KEY_OPTION}`.
 *
 * @internal WordPress action hook
 */
function invalidate_license_cache_hook(): void {
	delete_transient( LICENSE_STATUS_TRANSIENT );
}

/**
 * Get the license key from the option.
 *
 * Validates the license key option is a string or empty value. Non-strings
 * indicate a corrupted state and exceptions are thrown in these cases.
 *
 * @internal
 * @throws \UnexpectedValueException If the license key option is invalid.
 */
function get_license_key(): ?string {
	$license_key = get_option( LICENSE_KEY_OPTION );

	if ( false === $license_key ) {
		return null;
	}

	if ( is_null( $license_key ) ) {
		return null;
	}

	if ( ! is_string( $license_key ) ) {
		throw new \UnexpectedValueException( 'Invalid license key option value.' );
	}

	return $license_key;
}
