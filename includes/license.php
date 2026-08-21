<?php
/**
 * License functions.
 *
 * @package OutletPro
 * @subpackage License
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Minimum length for a valid stub license key.
 *
 * @internal
 */
const MIN_LICENSE_KEY_LENGTH = 2;

/**
 * HTTP OK response code.
 *
 * @internal
 */
const HTTP_OK = 200;

/**
 * HTTP Not Found response code.
 *
 * @internal
 */
const HTTP_NOT_FOUND = 404;

/**
 * Lemon Squeezy product IDs accepted for Outlet Pro licenses.
 *
 * @internal
 */
const ALLOWED_LICENSE_PRODUCT_IDS = array( 1279790 );

/**
 * WordPress option key used to store the license activation tuple.
 *
 * @internal
 */
const LICENSE_ACTIVATION_OPTION = 'outletpro_license_activation';

/**
 * Helper to initialize license features.
 *
 * @internal
 */
function init_license(): void {
	add_filter( 'plugin_action_links_' . plugin_basename( PLUGIN_FILE ), 'OutletPro\add_plugin_action_links_hook' );
}

/**
 * Helper to de-initialize license features back to the uninitialized state.
 *
 * @internal
 */
function deinit_license(): void {
	remove_filter( 'plugin_action_links_' . plugin_basename( PLUGIN_FILE ), 'OutletPro\add_plugin_action_links_hook' );
}

/**
 * Get the stored license activation.
 *
 * @internal
 * @return array{0: string, 1: string}|null The license key and activation ID, or null when not set.
 * @throws \UnexpectedValueException If the stored activation is invalid.
 */
function get_license_activation(): ?array {
	$activation = get_option( LICENSE_ACTIVATION_OPTION );

	if ( false === $activation ) {
		return null;
	}

	if ( ! is_array( $activation ) ) {
		throw new \UnexpectedValueException( 'Invalid license activation option value.' );
	}

	if ( array_keys( $activation ) !== array( 0, 1 ) ) {
		throw new \UnexpectedValueException( 'Invalid license activation option value.' );
	}

	if ( ! is_string( $activation[0] ) ) {
		throw new \UnexpectedValueException( 'Invalid license activation option value.' );
	}

	if ( '' === trim( $activation[0] ) ) {
		throw new \UnexpectedValueException( 'Invalid license activation option value.' );
	}

	if ( strlen( $activation[0] ) < MIN_LICENSE_KEY_LENGTH ) {
		throw new \UnexpectedValueException( 'Invalid license activation option value.' );
	}

	if ( ! is_string( $activation[1] ) ) {
		throw new \UnexpectedValueException( 'Invalid license activation option value.' );
	}

	if ( '' === trim( $activation[1] ) ) {
		throw new \UnexpectedValueException( 'Invalid license activation option value.' );
	}

	return $activation;
}

/**
 * Store a license activation.
 *
 * @internal
 * @param string $license_key The license key.
 * @param string $activation_id The Lemon Squeezy activation ID.
 * @throws \InvalidArgumentException If either value is invalid.
 */
function set_license_activation( string $license_key, string $activation_id ): void {
	if ( '' === trim( $license_key ) ) {
		throw new \InvalidArgumentException( 'Invalid license activation value.' );
	}

	if ( strlen( $license_key ) < MIN_LICENSE_KEY_LENGTH ) {
		throw new \InvalidArgumentException( 'Invalid license activation value.' );
	}

	if ( '' === trim( $activation_id ) ) {
		throw new \InvalidArgumentException( 'Invalid license activation value.' );
	}

	update_option( LICENSE_ACTIVATION_OPTION, array( $license_key, $activation_id ), false );
}

/**
 * Synchronize the stored activation option with the license key in settings.
 *
 * @internal
 */
function sync_activation(): void {
	$settings_license_key = get_license_key();

	// The license key is absent. Remove any stored activation.
	if ( is_null( $settings_license_key ) ) {
		delete_option( LICENSE_ACTIVATION_OPTION );
		return;
	}

	$activation_license_key = get_license_activation()[0] ?? null;

	// The license key and stored activation match. Do nothing.
	if ( $settings_license_key === $activation_license_key ) {
		return;
	}

	// Stored activation does not match the license key. Activate the license key.
	activate_license( $settings_license_key );
}

/**
 * Activate a license on this site.
 *
 * @internal
 *
 * @param string $license_key The license key.
 * @throws \RuntimeException If the activation request fails or the response is invalid.
 */
function activate_license( string $license_key ): bool {
	if ( '' === trim( $license_key ) ) {
		return false;
	}

	if ( ! validate_license( $license_key ) ) {
		return false;
	}

	$response = wp_remote_post(
		'https://api.lemonsqueezy.com/v1/licenses/activate',
		array(
			'timeout' => 5,
			'headers' => array(
				'Content-Type' => 'application/x-www-form-urlencoded',
				'Accept'       => 'application/json',
			),
			'body'    => array(
				'license_key'   => $license_key,
				'instance_name' => home_url(),
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		throw new \RuntimeException( 'License activation request failed' );
	}

	$status_code = wp_remote_retrieve_response_code( $response );

	if ( HTTP_OK !== $status_code ) {
		throw new \RuntimeException( 'License activation response code failed' );
	}

	$data = json_decode( wp_remote_retrieve_body( $response ) );

	if ( is_null( $data ) ) {
		throw new \RuntimeException( 'Could not decode JSON response' );
	}

	if ( ! is_bool( $data->activated ?? null ) ) {
		throw new \RuntimeException( 'Unexpected license activation response' );
	}

	if ( false === $data->activated ) {
		return false;
	}

	$activation_id = $data->instance->id ?? null;

	if ( ! is_string( $activation_id ) || '' === trim( $activation_id ) ) {
		throw new \RuntimeException( 'Unexpected license activation response' );
	}

	set_license_activation( $license_key, $activation_id );
	delete_transient( LICENSE_STATUS_TRANSIENT );

	return true;
}

/**
 * Deactivate the license on this site.
 *
 * @internal
 *
 * @param string $license_key The license key.
 * @param string $activation_id The activation ID.
 * @throws \RuntimeException If the deactivation request fails or the response is invalid.
 */
function deactivate_license( string $license_key, string $activation_id ): bool {
	if ( '' === trim( $license_key ) ) {
		return false;
	}

	if ( '' === trim( $activation_id ) ) {
		return false;
	}

	$response = wp_remote_post(
		'https://api.lemonsqueezy.com/v1/licenses/deactivate',
		array(
			'timeout' => 5,
			'headers' => array(
				'Content-Type' => 'application/x-www-form-urlencoded',
				'Accept'       => 'application/json',
			),
			'body'    => array(
				'license_key' => $license_key,
				'instance_id' => $activation_id,
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		throw new \RuntimeException( 'License deactivation request failed' );
	}

	$status_code = wp_remote_retrieve_response_code( $response );

	if ( HTTP_OK !== $status_code ) {
		throw new \RuntimeException( 'License deactivation response code failed' );
	}

	$data = json_decode( wp_remote_retrieve_body( $response ) );

	if ( is_null( $data ) ) {
		throw new \RuntimeException( 'Could not decode JSON response' );
	}

	if ( ! is_bool( $data->deactivated ?? null ) ) {
		throw new \RuntimeException( 'Unexpected license deactivation response' );
	}

	if ( false === $data->deactivated ) {
		return false;
	}

	delete_option( LICENSE_ACTIVATION_OPTION );
	delete_transient( LICENSE_STATUS_TRANSIENT );

	return true;
}

/**
 * Validate a license.
 *
 * A license key may be validated individually, or in combination with an activation ID.
 *
 * @internal
 *
 * @param string      $license_key The license key to validate.
 * @param string|null $activation_id The activation ID (optional).
 * @throws \RuntimeException If the license validation request fails or the response is invalid.
 */
function validate_license( string $license_key, ?string $activation_id = null ): bool {
	if ( '' === trim( $license_key ) ) {
		return false;
	}

	if ( strlen( $license_key ) < MIN_LICENSE_KEY_LENGTH ) {
		return false;
	}

	if ( ! is_null( $activation_id ) && '' === trim( $activation_id ) ) {
		return false;
	}

	$request_body = array(
		'license_key' => $license_key,
	);

	if ( ! is_null( $activation_id ) ) {
		$request_body['instance_id'] = $activation_id;
	}

	$response = wp_remote_post(
		'https://api.lemonsqueezy.com/v1/licenses/validate',
		array(
			'timeout' => 5,
			'headers' => array(
				'Content-Type' => 'application/x-www-form-urlencoded',
				'Accept'       => 'application/json',
			),
			'body'    => $request_body,
		)
	);

	if ( is_wp_error( $response ) ) {
		throw new \RuntimeException( 'License validation request failed' );
	}

	$status_code = wp_remote_retrieve_response_code( $response );

	if ( ! in_array( $status_code, array( HTTP_OK, HTTP_NOT_FOUND ), true ) ) {
		// Lemon Squeezy returns a 404 Not Found response for invalid license keys,
		// so it needs to be treated as an expected response code.
		throw new \RuntimeException( 'License validation response code failed' );
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( ! is_bool( $data['valid'] ?? null ) ) {
		throw new \RuntimeException( 'Unexpected license validation response' );
	}

	if ( false === $data['valid'] ) {
		// License key is invalid.
		return false;
	}

	if ( ! is_int( $data['meta']['product_id'] ?? null ) ) {
		throw new \RuntimeException( 'Unexpected license validation response' );
	}

	if ( ! in_array( $data['meta']['product_id'], ALLOWED_LICENSE_PRODUCT_IDS, true ) ) {
		throw new \RuntimeException( 'License not valid for allowed product IDs' );
	}

	return true;
}

/**
 * Get the license status.
 *
 * Performant function to get the license status, using a transient cache to
 * avoid repeated remote requests. Status is cached for 1 week, or 24 hours
 * if there was an error validating the license.
 *
 * Returns one of 'none', 'active', 'inactive', 'not_found', 'error', or 'expired'.
 *
 * none: No license key exists on this site or it is malformed.
 * active: The license key has been activated on this site.
 * inactive: The license key is valid but has not been activated on this site.
 * not_found: The license key is not recognized by the licenensing service.
 * error: There was an error with the licensing service when validating.
 * expired: The license key has expired.
 *
 * @internal
 * @see LICENSE_STATUS_TRANSIENT
 */
function get_license_status(): string {
	$cached_value = get_transient( LICENSE_STATUS_TRANSIENT );

	if ( false !== $cached_value ) {
		if (
			is_string( $cached_value )
			&& in_array( $cached_value, array( 'active', 'inactive', 'not_found', 'error', 'expired', 'none' ), true )
		) {
			return $cached_value;
		}

		// If the cached value is invalid, delete it and revalidate.
		delete_transient( LICENSE_STATUS_TRANSIENT );
	}

	try {
		$license_key = get_license_key();
	} catch ( \UnexpectedValueException $e ) {
		\wc_get_logger()->error( 'License key has invalid value.' );
		$license_key = null; // Proceed to handle malformed data as if empty.
	}

	if ( is_null( $license_key ) ) {
		set_transient( LICENSE_STATUS_TRANSIENT, 'none', WEEK_IN_SECONDS );
		return 'none';
	}

	try {
		$license_is_valid = validate_license( get_option( LICENSE_KEY_OPTION ) );
	} catch ( \RuntimeException $e ) {
		\wc_get_logger()->error( 'License status could not be retrieved.' );
		set_transient( LICENSE_STATUS_TRANSIENT, 'error', DAY_IN_SECONDS ); // Try again in 24 hours.
		return 'error';
	}

	$license_status = $license_is_valid ? 'active' : 'not_found';

	set_transient( LICENSE_STATUS_TRANSIENT, $license_status, WEEK_IN_SECONDS );

	return $license_status;
}

/**
 * Add a Setup link to the plugin's entry on the Plugins screen.
 *
 * Fired by `plugin_action_links_{plugin_basename}`.
 *
 * @param string[] $links Existing plugin action links.
 * @return string[] Modified plugin action links.
 * @internal WordPress filter
 */
function add_plugin_action_links_hook( array $links ): array {
	$setup_link = sprintf(
		'<a href="%s">%s</a>',
		esc_url( admin_url( 'admin.php?page=' . WELCOME_PAGE_SLUG ) ),
		esc_html__( 'Setup', 'outletpro' )
	);

	array_unshift( $links, $setup_link );

	return $links;
}
