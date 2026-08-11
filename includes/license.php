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
		'https://api.adrianduffell.store/v1/licenses/validate',
		array(
			'timeout' => 5,
			'headers' => array(
				'Content-Type' => 'application/json',
				'Accept'       => 'application/json',
			),
			'body'    => wp_json_encode(
				array(
					'license_key' => $license_key,
					'product'     => 'outletpro',
				)
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		throw new \RuntimeException( 'License validation request failed' );
	}

	if ( HTTP_OK !== wp_remote_retrieve_response_code( $response ) ) {
		throw new \RuntimeException( 'License validation response code failed' );
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( ! is_array( $data ) || ! isset( $data['success'] ) || ! is_bool( $data['success'] ) ) {
		throw new \RuntimeException( 'License validation response is invalid' );
	}

	return $data['success'];
}

/**
 * Check whether the site URL uses a common local development hostname.
 *
 * @internal
 */
function is_local_env(): bool {
	$hostname = wp_parse_url( get_site_url(), PHP_URL_HOST );

	if ( ! is_string( $hostname ) ) {
		return false;
	}

	$hostname = strtolower( $hostname );

	if ( in_array( $hostname, array( 'localhost', '127.0.0.1', '[::1]' ), true ) ) {
		return true;
	}

	return 1 === preg_match( '/\.(?:local|localhost|test)$/', $hostname );
}

/**
 * Check whether the current site has a valid license.
 *
 * @internal
 * @deprecated 1.1.0 Use `get_license_status()` instead.
 * @throws \RuntimeException If unable to check premium license.
 */
function has_license(): bool {
	$license_status = get_license_status();

	if ( 'error' === $license_status ) {
		throw new \RuntimeException( 'Unable to check premium license' );
	}

	return 'active' === $license_status;
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
