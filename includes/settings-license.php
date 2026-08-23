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
 * WordPress option name used to store the license activation tuple.
 *
 * @internal
 */
define( 'OutletPro\LICENSE_ACTIVATION_OPTION', 'outletpro_license_activation_' . safe_get_site_key() );

/**
 * WordPress transient key used to cache license status.
 *
 * @internal
 * @see get_license_status()
 */
define( 'OutletPro\LICENSE_STATUS_TRANSIENT', 'outletpro_license_status_' . safe_get_site_key() );

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
	add_action( 'add_option_' . LICENSE_KEY_OPTION, 'OutletPro\add_license_activation_hook', 10, 0 );
	add_action( 'update_option_' . LICENSE_KEY_OPTION, 'OutletPro\update_license_activation_hook', 10, 0 );
	add_action( 'delete_option_' . LICENSE_KEY_OPTION, 'OutletPro\delete_license_activation_hook', 10, 0 );
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
	remove_action( 'add_option_' . LICENSE_KEY_OPTION, 'OutletPro\add_license_activation_hook', 10, 0 );
	remove_action( 'update_option_' . LICENSE_KEY_OPTION, 'OutletPro\update_license_activation_hook', 10, 0 );
	remove_action( 'delete_option_' . LICENSE_KEY_OPTION, 'OutletPro\delete_license_activation_hook', 10, 0 );
}

/**
 * Synchronizes the activation tuple when the license key option is first added.
 *
 * Fired by `add_option_{LICENSE_KEY_OPTION}`.
 *
 * @internal WordPress action hook
 */
function add_license_activation_hook(): void {
	try {
		sync_activation();
	} catch ( \Throwable $e ) {
		\wc_get_logger()->error( 'License activation could not be synchronized when setting added.' );
	}
}

/**
 * Synchronizes the activation tuple when the license key option is changed.
 *
 * Fired by `update_option_{LICENSE_KEY_OPTION}`.
 *
 * @internal WordPress action hook
 */
function update_license_activation_hook(): void {
	try {
		sync_activation();
	} catch ( \Throwable $e ) {
		\wc_get_logger()->error( 'License activation could not be synchronized when setting changed.' );
	}
}

/**
 * Synchronizes the activation tuple when the license key option is deleted.
 *
 * Fired by `delete_option_{LICENSE_KEY_OPTION}`.
 *
 * @internal WordPress action hook
 */
function delete_license_activation_hook(): void {
	try {
		sync_activation();
	} catch ( \Throwable $e ) {
		\wc_get_logger()->error( 'License activation could not be synchronized when setting deleted.' );
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
 * Returns null when the license key option does not exist or is an empty string.
 * Other non-strings, including null, indicate a corrupted state.
 *
 * @internal
 * @throws \UnexpectedValueException If the license key option is invalid.
 */
function get_license_key(): ?string {
	$license_key = get_option( LICENSE_KEY_OPTION );

	// Option does not exist.
	if ( false === $license_key ) {
		return null;
	}

	// Normalize empty string.
	if ( '' === $license_key ) {
		return null;
	}

	if ( ! is_string( $license_key ) ) {
		throw new \UnexpectedValueException( 'Invalid license key option value.' );
	}

	return $license_key;
}

/**
 * Get a unique key for the site based on the hostname of the home URL.
 *
 * @internal
 * @return string The key for the site.
 * @throws \UnexpectedValueException If the site key cannot be determined.
 */
function get_site_key(): string {
	$home_url = home_url();

	if ( ! is_string( $home_url ) ) {
		throw new \UnexpectedValueException( 'Invalid home URL.' );
	}

	$domain = wp_parse_url( $home_url, PHP_URL_HOST );

	if ( ! is_string( $domain ) ) {
		throw new \UnexpectedValueException( 'Invalid home URL hostname.' );
	}

	$site_key = sanitize_key( str_replace( '.', '_', $domain ) );

	if ( ! is_string( $site_key ) ) {
		throw new \UnexpectedValueException( 'Invalid site key.' );
	}

	if ( '' === $site_key ) {
		throw new \UnexpectedValueException( 'Invalid site key.' );
	}

	return substr( $site_key, -80 );
}

/**
 * Safely call get_site_key() and return its response or "invalid" string on exception.
 *
 * @internal
 * @return string The site key, or 'invalid' on failure.
 */
function safe_get_site_key(): string {
	try {
		return get_site_key();
	} catch ( \Throwable $e ) {
		return 'invalid';
	}
}
