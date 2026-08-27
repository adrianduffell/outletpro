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
 * Minimum length for a valid stub license key.
 *
 * @internal
 */
const MIN_LICENSE_KEY_LENGTH = 2;

/**
 * License not found error code.
 *
 * @internal
 */
const LICENSE_ERROR_NOT_FOUND = 'not_found';

/**
 * License expired error code.
 *
 * @internal
 */
const LICENSE_ERROR_EXPIRED = 'expired';

/**
 * HTTP Bad Request response code.
 *
 * @internal
 */
const HTTP_BAD_REQUEST = 400;

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
 * Request-local cache group for license validation responses.
 *
 * @internal
 */
const LICENSE_HTTP_CACHE_GROUP = 'outletpro_license_http';

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
 * WordPress transient key used to cache the license variant name.
 *
 * @internal
 * @see get_license_name()
 */
define( 'OutletPro\LICENSE_NAME_TRANSIENT', 'outletpro_license_name_' . safe_get_site_key() );

/**
 * WordPress transient key used to cache the license expiry.
 *
 * The transient value is an array tuple where:
 * 0: Is a boolean indicating whether the license has an expiry (true) or is perpetual (false).
 * 1: Is a string containing the expiry date in ISO 8601 format, or not defined if perpetual.
 *
 * @internal
 */
define( 'OutletPro\LICENSE_EXPIRY_TRANSIENT', 'outletpro_license_expiry_' . safe_get_site_key() );

/**
 * Helper to initialize license settings.
 *
 * @internal
 */
function init_license_settings(): void {
	wp_cache_add_non_persistent_groups( LICENSE_HTTP_CACHE_GROUP );
	register_license_key_setting();

	add_action( 'add_option_' . LICENSE_KEY_OPTION, 'OutletPro\add_license_activation_hook', 10, 0 );
	add_action( 'update_option_' . LICENSE_KEY_OPTION, 'OutletPro\update_license_activation_hook', 10, 0 );
	add_action( 'deleted_option', 'OutletPro\delete_license_activation_hook', 10, 1 );
}

/**
 * Helper to deinitialize license settings.
 *
 * @internal
 */
function deinit_license_settings(): void {
	unregister_setting( LICENSE_OPTIONS_GROUP, LICENSE_KEY_OPTION );
	remove_action( 'add_option_' . LICENSE_KEY_OPTION, 'OutletPro\add_license_activation_hook', 10, 0 );
	remove_action( 'update_option_' . LICENSE_KEY_OPTION, 'OutletPro\update_license_activation_hook', 10, 0 );
	remove_action( 'deleted_option', 'OutletPro\delete_license_activation_hook', 10 );
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
 * Fired by `deleted_option`.
 *
 * @internal WordPress action hook
 * @param string $option The deleted option name.
 */
function delete_license_activation_hook( string $option ): void {
	if ( LICENSE_KEY_OPTION !== $option ) {
		return;
	}

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
 * The license key in settings is treated as the source of truth. A sync is
 * required when one of:
 *
 * 1. The license key in settings is added.
 *   - Perform activation.
 *
 * 2. The license key in settings is updated.
 *   - Clean up any stale activation
 *   - Perform activation.
 *
 * 3. The license key in settings is deleted.
 *   - Clean-up of stale activation
 *
 * @internal
 */
function sync_activation(): void {
	$settings_license_key   = get_license_key();
	$license_activation     = get_license_activation();
	$activation_license_key = $license_activation[0] ?? null;

	// License setting and activation are in sync. Nothing to do.
	if ( $settings_license_key === $activation_license_key ) {
		return;
	}

	delete_transient( LICENSE_STATUS_TRANSIENT );

	// Cases 2 or 3: Clean up any stale activation.
	if ( ! is_null( $license_activation ) ) {
		deactivate_license( ...$license_activation );
	}
	delete_option( LICENSE_ACTIVATION_OPTION );

	// Case 3: No activation required.
	if ( is_null( $settings_license_key ) ) {
		return;
	}

	// Case 1 or 2: Perform activation.
	$activation_id = activate_license( $settings_license_key );
	set_license_activation( $settings_license_key, $activation_id );
}

/**
 * Activate a license on this site.
 *
 * @internal
 *
 * @param string $license_key The license key.
 * @return string The Lemon Squeezy activation ID.
 * @throws \InvalidArgumentException If the license key is invalid.
 * @throws \RuntimeException If the activation request fails or the response is invalid.
 */
function activate_license( string $license_key ): string {
	if ( '' === trim( $license_key ) ) {
		throw new \InvalidArgumentException( 'License key cannot be empty.' );
	}

	$validation_result = validate_license( $license_key );

	if ( is_wp_error( $validation_result ) ) {
		throw new \RuntimeException( 'License is invalid.' );
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
		throw new \RuntimeException( 'License activation was rejected.' );
	}

	$activation_id = $data->instance->id ?? null;

	if ( ! is_string( $activation_id ) || '' === trim( $activation_id ) ) {
		throw new \RuntimeException( 'Unexpected license activation response' );
	}

	return $activation_id;
}

/**
 * Deactivate the license on this site.
 *
 * @internal
 *
 * @param string $license_key The license key.
 * @param string $activation_id The activation ID.
 * @throws \InvalidArgumentException If the license key or activation ID is invalid.
 * @throws \RuntimeException If the deactivation request fails or the response is invalid.
 */
function deactivate_license( string $license_key, string $activation_id ): void {
	if ( '' === trim( $license_key ) ) {
		throw new \InvalidArgumentException( 'License key cannot be empty.' );
	}

	if ( '' === trim( $activation_id ) ) {
		throw new \InvalidArgumentException( 'Activation ID cannot be empty.' );
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
		throw new \RuntimeException( 'License deactivation was rejected.' );
	}
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
 * @return true|\WP_Error True when the licensing service determines it is valid, otherwise WP_Error.
 * @throws \InvalidArgumentException If the license key is empty or too short.
 * @throws \InvalidArgumentException If the activation ID is provided but empty.
 * @throws \RuntimeException If the license validation request fails or the response is invalid.
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
 */
function validate_license( string $license_key, ?string $activation_id = null ) {
	if ( '' === trim( $license_key ) ) {
		throw new \InvalidArgumentException( 'License key must not be empty.' );
	}

	if ( strlen( $license_key ) < MIN_LICENSE_KEY_LENGTH ) {
		throw new \InvalidArgumentException( 'License key is too short.' );
	}

	if ( ! is_null( $activation_id ) && '' === trim( $activation_id ) ) {
		throw new \InvalidArgumentException( 'Activation ID must not be empty.' );
	}

	$request_body = array(
		'license_key' => $license_key,
	);

	if ( ! is_null( $activation_id ) ) {
		$request_body['instance_id'] = $activation_id;
	}

	$cache_key = hash( 'sha256', $license_key . ( $activation_id ?? '' ) );
	$response  = wp_cache_get( $cache_key, LICENSE_HTTP_CACHE_GROUP )
		?: wp_remote_post( // phpcs:ignore Universal.Operators.DisallowShortTernary.Found
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

	wp_cache_set( $cache_key, $response, LICENSE_HTTP_CACHE_GROUP );

	if ( is_wp_error( $response ) ) {
		throw new \RuntimeException( 'License validation request failed' );
	}

	$status_code = wp_remote_retrieve_response_code( $response );

	// Throw an exception for unexpected response codes. Note: Lemon Squeezy returns
	// 400 for expired and 404 for not_found, so these are considered expected.
	if ( ! in_array( $status_code, array( HTTP_OK, HTTP_BAD_REQUEST, HTTP_NOT_FOUND ), true ) ) {
		throw new \RuntimeException( 'License validation response code failed' );
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( ! is_bool( $data['valid'] ?? null ) ) {
		throw new \RuntimeException( 'Unexpected license validation response' );
	}

	if ( false === $data['valid'] ) {
		if ( 'expired' === ( $data['license_key']['status'] ?? null ) ) {
			return new \WP_Error( LICENSE_ERROR_EXPIRED );
		}

		return new \WP_Error( LICENSE_ERROR_NOT_FOUND );
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
 * Returns one of 'none', 'active', 'not_found', 'error', or 'expired'.
 *
 * none: No license key activation exists on this site or the record is malformed.
 * active: The license key has been activated on this site.
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
			&& in_array( $cached_value, array( 'active', 'not_found', 'error', 'expired', 'none' ), true )
		) {
			return $cached_value;
		}

		// If the cached value is invalid, delete it and revalidate.
		delete_transient( LICENSE_STATUS_TRANSIENT );
	}

	try {
		$license_activation = get_license_activation();
	} catch ( \UnexpectedValueException $e ) {
		\wc_get_logger()->error( 'License activation has invalid value.' );
		$license_activation = null; // Proceed to handle malformed data as if empty.
	}

	if ( is_null( $license_activation ) ) {
		set_transient( LICENSE_STATUS_TRANSIENT, 'none', WEEK_IN_SECONDS );
		return 'none';
	}

	try {
		$validation_result = validate_license( ...$license_activation );
	} catch ( \RuntimeException $e ) {
		\wc_get_logger()->error( 'License status could not be retrieved.' );
		set_transient( LICENSE_STATUS_TRANSIENT, 'error', DAY_IN_SECONDS ); // Try again in 24 hours.
		return 'error';
	}

	$license_status = is_wp_error( $validation_result ) ? $validation_result->get_error_code() : 'active';

	set_transient( LICENSE_STATUS_TRANSIENT, $license_status, WEEK_IN_SECONDS );

	return $license_status;
}

/**
 * Get the name of the license activated on the site.
 *
 * @internal
 * @see LICENSE_NAME_TRANSIENT
 * @throws \RuntimeException If the site is not activated with a license.
 * @throws \RuntimeException If the license validation request fails.
 * @throws \UnexpectedValueException If the license activation is unavailable, or the license name is missing/invalid.
 */
function get_license_name(): string {
	if ( in_array( get_license_status(), array( 'none', 'not_found' ), true ) ) {
		throw new \RuntimeException( 'Site is not activated with license.' );
	}

	$cached_value = get_transient( LICENSE_NAME_TRANSIENT );

	if ( is_string( $cached_value ) && '' !== trim( $cached_value ) ) {
		return $cached_value;
	}

	$license_activation = get_license_activation();

	if ( is_null( $license_activation ) ) {
		throw new \UnexpectedValueException( 'License is unavailable.' );
	}

	$cache_key = hash( 'sha256', $license_activation[0] . $license_activation[1] );
	$response  = wp_cache_get( $cache_key, LICENSE_HTTP_CACHE_GROUP )
		?: wp_remote_post( // phpcs:ignore Universal.Operators.DisallowShortTernary.Found
			'https://api.lemonsqueezy.com/v1/licenses/validate',
			array(
				'timeout' => 5,
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
					'Accept'       => 'application/json',
				),
				'body'    => array(
					'license_key' => $license_activation[0],
					'instance_id' => $license_activation[1],
				),
			)
		);

	wp_cache_set( $cache_key, $response, LICENSE_HTTP_CACHE_GROUP );

	if ( is_wp_error( $response ) ) {
		throw new \RuntimeException( 'License validation request failed' );
	}

	if ( ! in_array(
		wp_remote_retrieve_response_code( $response ),
		array( HTTP_OK, HTTP_BAD_REQUEST, HTTP_NOT_FOUND ),
		true
	)
	) {
		throw new \RuntimeException( 'License validation response code failed' );
	}

	$data         = json_decode( wp_remote_retrieve_body( $response ), true );
	$license_name = $data['meta']['variant_name'] ?? null;

	if ( ! is_string( $license_name ) ) {
		throw new \UnexpectedValueException( 'Unexpected license name type.' );
	}

	if ( '' === trim( $license_name ) ) {
		throw new \UnexpectedValueException( 'License name is empty' );
	}

	// Set the transient with no scheduled expiry since the name is not expected to change.
	set_transient( LICENSE_NAME_TRANSIENT, $license_name, 0 );

	return $license_name;
}

/**
 * Get the expiry of the license activated on the site.
 *
 * @internal
 * @see LICENSE_EXPIRY_TRANSIENT
 * @throws \RuntimeException If the site is not activated with a license.
 * @throws \RuntimeException If the license validation request fails.
 * @throws \UnexpectedValueException If the license is unavailable or has an invalid expiry.
 */
function get_license_expiry(): ?\DateTimeImmutable {
	if ( in_array( get_license_status(), array( 'none', 'not_found' ), true ) ) {
		throw new \RuntimeException( 'Site is not activated with license.' );
	}

	$transient = get_transient( LICENSE_EXPIRY_TRANSIENT );

	// License has no expiry.
	if ( is_array( $transient ) && false === ( $transient[0] ?? null ) ) {
		return null;
	}

	// License with expiry.
	if (
		is_array( $transient )
		&& true === ( $transient[0] ?? null )
		&& is_string( $transient[1] ?? null )
	) {
		try {
			return new \DateTimeImmutable( $transient[1] ); // Convert ISO date.
		} catch ( \Exception $e ) {
			delete_transient( LICENSE_EXPIRY_TRANSIENT );
		}
	}

	$license_activation = get_license_activation();

	if ( is_null( $license_activation ) ) {
		throw new \UnexpectedValueException( 'License is unavailable.' );
	}

	$cache_key = hash( 'sha256', $license_activation[0] . $license_activation[1] );
	$response  = wp_cache_get( $cache_key, LICENSE_HTTP_CACHE_GROUP )
		?: wp_remote_post( // phpcs:ignore Universal.Operators.DisallowShortTernary.Found
			'https://api.lemonsqueezy.com/v1/licenses/validate',
			array(
				'timeout' => 5,
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
					'Accept'       => 'application/json',
				),
				'body'    => array(
					'license_key' => $license_activation[0],
					'instance_id' => $license_activation[1],
				),
			)
		);

	wp_cache_set( $cache_key, $response, LICENSE_HTTP_CACHE_GROUP );

	if ( is_wp_error( $response ) ) {
		throw new \RuntimeException( 'License validation request failed' );
	}

	if ( ! in_array(
		wp_remote_retrieve_response_code( $response ),
		array( HTTP_OK, HTTP_BAD_REQUEST, HTTP_NOT_FOUND ),
		true
	) ) {
		throw new \RuntimeException( 'License validation response code failed' );
	}

	$data = json_decode( wp_remote_retrieve_body( $response ) );

	if ( ! is_object( $data->license_key ?? null ) ) {
		throw new \UnexpectedValueException( 'Unexpected license type.' );
	}

	if ( ! property_exists( $data->license_key, 'expires_at' ) ) {
		throw new \UnexpectedValueException( 'License expiry is missing.' );
	}

	// License has no expiry.
	if ( is_null( $data->license_key->expires_at ) ) {
		set_transient( LICENSE_EXPIRY_TRANSIENT, array( false ), DAY_IN_SECONDS );
		return null;
	}

	if ( ! is_string( $data->license_key->expires_at ) ) {
		throw new \UnexpectedValueException( 'Unexpected license expiry type.' );
	}

	if ( '' === trim( $data->license_key->expires_at ) ) {
		throw new \UnexpectedValueException( 'License expiry is empty.' );
	}

	try {
		$license_expiry_date = new \DateTimeImmutable( $data->license_key->expires_at );
	} catch ( \Exception $e ) {
		throw new \UnexpectedValueException( 'License expiry is malformed.' );
	}

	set_transient( LICENSE_EXPIRY_TRANSIENT, array( true, $data->license_key->expires_at ), DAY_IN_SECONDS );

	return $license_expiry_date;
}
