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
 * WordPress option key used to store the Lemon Squeezy activation ID.
 *
 * This option is deliberately not registered as a setting because it is
 * managed internally rather than directly by users.
 *
 * @internal
 */
const LICENSE_ACTIVATION_ID_OPTION = 'outletpro_license_activation_id';

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

	$data = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( ! is_bool( $data['activated'] ?? null ) ) {
		throw new \RuntimeException( 'Unexpected license activation response' );
	}

	if ( false === $data['activated'] ) {
		return false;
	}

	$activation_id = $data['instance']['id'] ?? null;

	if ( ! is_string( $activation_id ) || '' === trim( $activation_id ) ) {
		throw new \RuntimeException( 'Unexpected license activation response' );
	}

	update_option( LICENSE_ACTIVATION_ID_OPTION, $activation_id, false );
	delete_transient( LICENSE_STATUS_TRANSIENT );

	return true;
}

/**
 * Validate a license key.
 *
 * @internal
 *
 * @param mixed $license_key The license key to validate.
 * @throws \RuntimeException If the license validation request fails or the response is invalid.
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
 */
function validate_license( $license_key ): bool {
	if ( ! is_string( $license_key ) ) {
		return false;
	}

	if ( '' === trim( $license_key ) ) {
		return false;
	}

	if ( strlen( $license_key ) < MIN_LICENSE_KEY_LENGTH ) {
		return false;
	}

	$response = wp_remote_post(
		'https://api.lemonsqueezy.com/v1/licenses/validate',
		array(
			'timeout' => 5,
			'headers' => array(
				'Content-Type' => 'application/x-www-form-urlencoded',
				'Accept'       => 'application/json',
			),
			'body'    => array(
				'license_key' => $license_key,
			),
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
 * Returns one of 'active', 'inactive', 'not_found', 'error', or 'expired'.
 *
 * active: The license key has been activated on this site.
 * inactive: The license key is valid but has not been activated on this site.
 * not_found: The license key is not recognized by the server.
 * error: There was an error validating the license key.
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
			&& in_array( $cached_value, array( 'active', 'inactive', 'not_found', 'error', 'expired' ), true )
		) {
			return $cached_value;
		}

		// If the cached value is invalid, delete it and revalidate.
		delete_transient( LICENSE_STATUS_TRANSIENT );
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
 * Add a Settings link to the plugin's entry on the Plugins screen.
 *
 * Fired by `plugin_action_links_{plugin_basename}`.
 *
 * @param string[] $links Existing plugin action links.
 * @return string[] Modified plugin action links.
 * @internal WordPress filter
 */
function add_plugin_action_links_hook( array $links ): array {
	$settings_link = sprintf(
		'<a href="%s">%s</a>',
		esc_url( admin_url( 'admin.php?page=' . LICENSE_OPTIONS_GROUP ) ),
		esc_html__( 'License', 'outletpro' )
	);

	array_unshift( $links, $settings_link );

	return $links;
}

/**
 * Render the license settings page.
 *
 * @internal
 */
function render_license_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<p>
			<?php esc_html_e( 'Your premium license provides plugin updates and email support.', 'outletpro' ); ?>
			<a href="<?php echo esc_url( 'https://outletpro.zip/' ); ?>">
				<?php esc_html_e( 'Learn more', 'outletpro' ); ?>
			</a>
		</p>
		<form method="post" action="options.php">
			<?php settings_fields( LICENSE_OPTIONS_GROUP ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="<?php echo esc_attr( LICENSE_KEY_OPTION ); ?>"><?php esc_html_e( 'Premium license key', 'outletpro' ); ?></label>
					</th>
					<td>
						<input
							type="text"
							id="<?php echo esc_attr( LICENSE_KEY_OPTION ); ?>"
							name="<?php echo esc_attr( LICENSE_KEY_OPTION ); ?>"
							value="<?php echo esc_attr( (string) get_option( LICENSE_KEY_OPTION, '' ) ); ?>"
							placeholder="XXXX-XXXX-XXXX-XXXX"
							class="regular-text"
						/>
						<p class="description"><?php esc_html_e( 'Enter your premium license key.', 'outletpro' ); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
