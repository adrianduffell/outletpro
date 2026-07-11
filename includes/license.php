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
 * Admin page slug for the license settings page.
 *
 * @internal
 */
const LICENSE_PAGE_SLUG = 'outletpro-license';

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
		return 1 === $cached_value;
	}

	$license_is_valid = validate_license( get_option( LICENSE_KEY_OPTION ) );

	set_transient( HAS_LICENSE_TRANSIENT, $license_is_valid ? 1 : 0, WEEK_IN_SECONDS );

	return $license_is_valid;
}

/**
 * Helper to initialize license features.
 *
 * @internal
 */
function init_license(): void {
	add_action( 'admin_menu', 'OutletPro\add_license_menu_hook' );
	add_filter( 'plugin_action_links_' . plugin_basename( PLUGIN_FILE ), 'OutletPro\add_plugin_action_links_hook' );
}

/**
 * Helper to de-initialize license features back to the uninitialized state.
 *
 * @internal
 */
function deinit_license(): void {
	remove_action( 'admin_menu', 'OutletPro\add_license_menu_hook' );
	remove_filter( 'plugin_action_links_' . plugin_basename( PLUGIN_FILE ), 'OutletPro\add_plugin_action_links_hook' );
}

/**
 * Register the license key setting.
 *
 * @internal
 */
function register_license_key_setting(): void {
	register_setting(
		LICENSE_PAGE_SLUG,
		LICENSE_KEY_OPTION,
		array(
			'type'              => 'string',
			'label'             => __( 'License Key', 'outletpro' ),
			'description'       => __( 'Outlet Pro license key.', 'outletpro' ),
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
}

/**
 * Register a hidden license settings admin page, not linked in any menu.
 *
 * Fired by `admin_menu`.
 *
 * @internal WordPress action hook
 */
function add_license_menu_hook(): void {
	add_submenu_page(
		'options.php',
		__( 'Outlet Pro License', 'outletpro' ),
		__( 'Outlet Pro', 'outletpro' ),
		'manage_options',
		LICENSE_PAGE_SLUG,
		'OutletPro\render_license_page'
	);
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
