<?php
/**
 * License functions.
 *
 * @package OutletPro
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * WordPress option key used to store the license key.
 *
 * @internal
 */
const LICENSE_KEY_OPTION = 'outletpro_license_key';

/**
 * WordPress transient key used to cache license validity.
 *
 * @internal
 */
const HAS_LICENSE_TRANSIENT = 'outletpro_has_license';

/**
 * Minimum length for a valid stub license key.
 *
 * @internal
 */
const MIN_LICENSE_KEY_LENGTH = 2;

/**
 * Helper to initialize license features.
 *
 * @internal
 */
function init_license(): void {
	add_filter( 'plugin_action_links_' . plugin_basename( PLUGIN_FILE ), 'OutletPro\add_plugin_action_links_hook' );
	add_action( 'add_option_' . LICENSE_KEY_OPTION, 'OutletPro\invalidate_license_cache_hook', 10, 0 );
	add_action( 'update_option_' . LICENSE_KEY_OPTION, 'OutletPro\invalidate_license_cache_hook', 10, 0 );
}

/**
 * Helper to de-initialize license features back to the uninitialized state.
 *
 * @internal
 */
function deinit_license(): void {
	remove_filter( 'plugin_action_links_' . plugin_basename( PLUGIN_FILE ), 'OutletPro\add_plugin_action_links_hook' );
	remove_action( 'add_option_' . LICENSE_KEY_OPTION, 'OutletPro\invalidate_license_cache_hook' );
	remove_action( 'update_option_' . LICENSE_KEY_OPTION, 'OutletPro\invalidate_license_cache_hook' );
}

/**
 * Validate a license key.
 *
 * @internal
 *
 * @param mixed $license_key The license key to validate.
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
 */
function validate_license( $license_key ): bool {
	return is_string( $license_key ) && strlen( $license_key ) >= MIN_LICENSE_KEY_LENGTH;
}

/**
 * Check whether the current site has a valid license.
 *
 * @internal
 */
function has_license(): bool {
	$cached_value = get_transient( HAS_LICENSE_TRANSIENT );

	if ( false !== $cached_value ) {
		return 'yes' === $cached_value;
	}

	$license_is_valid = validate_license( get_option( LICENSE_KEY_OPTION ) );

	set_transient( HAS_LICENSE_TRANSIENT, $license_is_valid ? 'yes' : 'no', WEEK_IN_SECONDS );

	return $license_is_valid;
}

/**
 * Invalidate the license cache when the license key option is added or updated.
 *
 * Fired by `add_option_{LICENSE_KEY_OPTION}` and `update_option_{LICENSE_KEY_OPTION}`.
 *
 * @internal WordPress action hook
 */
function invalidate_license_cache_hook(): void {
	delete_transient( HAS_LICENSE_TRANSIENT );
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
		esc_url( admin_url( 'admin.php?page=' . LICENSE_PAGE_SLUG ) ),
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
			<?php settings_fields( LICENSE_PAGE_SLUG ); ?>
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
