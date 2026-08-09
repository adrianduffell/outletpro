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
	add_action( 'after_plugin_row_' . plugin_basename( PLUGIN_FILE ), 'OutletPro\add_premium_license_notice_hook', 10, 3 );
}

/**
 * Helper to de-initialize license features back to the uninitialized state.
 *
 * @internal
 */
function deinit_license(): void {
	remove_filter( 'plugin_action_links_' . plugin_basename( PLUGIN_FILE ), 'OutletPro\add_plugin_action_links_hook' );
	remove_action( 'after_plugin_row_' . plugin_basename( PLUGIN_FILE ), 'OutletPro\add_premium_license_notice_hook', 10 );
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
 * Check whether the current site has a valid license.
 *
 * @internal
 * @throws \RuntimeException If unable to check premium license.
 */
function has_license(): bool {
	$cached_value = get_transient( HAS_LICENSE_TRANSIENT );

	if ( false !== $cached_value ) {
		return 'yes' === $cached_value;
	}

	try {
		$license_is_valid = validate_license( get_option( LICENSE_KEY_OPTION ) );
	} catch ( \RuntimeException $e ) {
		throw new \RuntimeException( 'Unable to check premium license' );
	}

	set_transient( HAS_LICENSE_TRANSIENT, $license_is_valid ? 'yes' : 'no', WEEK_IN_SECONDS );

	return $license_is_valid;
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
 * Add a premium license notice row to the plugin screen.
 *
 * Fired by `after_plugin_row_{plugin_basename}`.
 *
 * @param string               $plugin_file The plugin file path.
 * @param array<string, mixed> $plugin_data Plugin metadata.
 * @param string               $status      Plugin status.
 * @internal WordPress action hook
 */
function add_premium_license_notice_hook( string $plugin_file, array $plugin_data, string $status ): void {
	unset( $plugin_file, $plugin_data, $status );

	try {
		if ( has_license() ) {
			return;
		}
	} catch ( \RuntimeException $e ) {
		\wc_get_logger()->error( 'Could not check premium license to render plugin notice' );
		return;
	}
	?>
	<tr class="plugin-update-tr outletpro-license-notice">
		<td colspan="3" class="plugin-update colspanchange">
			<div class="update-message notice inline notice-warning notice-alt">
				<p>
					<?php esc_html_e( 'Outlet Pro requires a premium license for plugin updates.', 'outletpro' ); ?>
					<a class="button-link" href="<?php echo esc_url( 'https://outletpro.zip/premium-license' ); ?>" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Learn more', 'outletpro' ); ?>
					</a>
				</p>
			</div>
		</td>
	</tr>
	<?php
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
