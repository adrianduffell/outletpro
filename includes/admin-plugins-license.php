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
 * Helper to initialize license features.
 *
 * @internal
 */
function init_license(): void {
	add_filter( 'plugin_action_links_' . plugin_basename( PLUGIN_FILE ), 'OutletPro\add_plugin_action_links_hook' );
	add_action( 'after_plugin_row_' . plugin_basename( PLUGIN_FILE ), 'OutletPro\add_premium_license_notice_hook', 10, 3 );
	add_filter( 'plugin_auto_update_setting_html', 'OutletPro\add_auto_update_unavailable_label_hook', 10, 3 );
	add_filter( 'plugin_row_meta', 'OutletPro\add_plugin_meta_links_hook', 10, 2 );
	add_filter( 'plugin_row_meta', 'OutletPro\add_plugin_license_expiry_hook', 9999, 2 );
}

/**
 * Helper to de-initialize license features back to the uninitialized state.
 *
 * @internal
 */
function deinit_license(): void {
	remove_filter( 'plugin_action_links_' . plugin_basename( PLUGIN_FILE ), 'OutletPro\add_plugin_action_links_hook' );
	remove_action( 'after_plugin_row_' . plugin_basename( PLUGIN_FILE ), 'OutletPro\add_premium_license_notice_hook', 10 );
	remove_filter( 'plugin_auto_update_setting_html', 'OutletPro\add_auto_update_unavailable_label_hook' );
	remove_filter( 'plugin_row_meta', 'OutletPro\add_plugin_meta_links_hook' );
	remove_filter( 'plugin_row_meta', 'OutletPro\add_plugin_license_expiry_hook', 9999 );
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

	$license_status = get_license_status();

	if ( 'active' === $license_status ) {
		return;
	}

	if ( 'error' === $license_status ) {
		return;
	}
	?>
	<tr class="plugin-update-tr active outletpro-license-notice">
		<td colspan="4" class="plugin-update colspanchange">
			<div class="update-message notice inline notice-warning notice-alt">
				<p><?php esc_html_e( 'A premium license is needed for Outlet Pro to receive updates.', 'outletpro' ); ?>
					<a class="button-link" href="<?php echo esc_url( admin_url( 'admin.php?page=outletpro-welcome' ) ); ?>">
						<?php esc_html_e( 'Set up premium license', 'outletpro' ); ?>
					</a>
				</p>
			</div>
		</td>
	</tr>
	<?php
}

/**
 * Adds an "Auto-updates unavailable" label to the plugin admin table row when the
 * license is not active.
 *
 * Fired by `plugin_auto_update_setting_html`.
 *
 * @param string               $html        Existing auto-update setting HTML.
 * @param string               $plugin_file Plugin file relative to the plugins directory.
 * @param array<string, mixed> $plugin_data Plugin metadata.
 * @return string Modified auto-update setting HTML.
 * @internal WordPress filter
 */
function add_auto_update_unavailable_label_hook(
	string $html,
	string $plugin_file,
	array $plugin_data
): string {
	if ( plugin_basename( PLUGIN_FILE ) !== $plugin_file ) {
		return $html;
	}

	if ( isset( $plugin_data['auto-update-forced'] ) ) {
		return $html;
	}

	if ( ! empty( $plugin_data['update-supported'] ) ) {
		return $html;
	}

	$license_status = get_license_status();

	if ( 'active' === $license_status ) {
		return $html;
	}

	if ( 'error' === $license_status ) {
		return $html;
	}

	return sprintf(
		'<span class="label">%s</span>',
		esc_html__( 'Auto-updates unavailable', 'outletpro' )
	);
}

/**
 * Add a Support link to the plugin's meta links on the Plugins screen.
 *
 * Fired by `plugin_row_meta`.
 *
 * @param string[] $links       Existing plugin meta links.
 * @param string   $plugin_file Path to the plugin file relative to the plugins directory.
 * @return string[] Modified plugin meta links.
 * @internal WordPress filter
 */
function add_plugin_meta_links_hook( array $links, string $plugin_file ): array {
	if ( plugin_basename( PLUGIN_FILE ) !== $plugin_file ) {
		return $links;
	}

	$links[] = sprintf(
		'<a href="%s">%s</a>',
		esc_url( 'https://outletpro.zip/support' ),
		esc_html__( 'Support', 'outletpro' )
	);

	return $links;
}

/**
 * Add license expiry details alongside the plugin’s meta links.
 *
 * Fired by `plugin_row_meta` after the plugin's other meta links are added.
 *
 * @param string[] $links       Existing plugin meta links.
 * @param string   $plugin_file Plugin file.
 * @return string[] Modified plugin meta links.
 * @internal WordPress filter
 */
function add_plugin_license_expiry_hook( array $links, string $plugin_file ): array {
	if ( plugin_basename( PLUGIN_FILE ) !== $plugin_file ) {
		return $links;
	}

	$license_status = get_license_status();

	switch ( $license_status ) {
		case 'none':
		case 'not_found':
			return $links;
	}

	try {
		$license_name   = get_license_name();
		$license_expiry = get_license_expiry();
	} catch ( \Throwable $e ) {
		return $links;
	}

	if ( is_null( $license_expiry ) ) {
		$links[] = sprintf(
			/* translators: %s: license name. */
			'<span class="outletpro-license-expiry">' . esc_html__( '%s (non-expiring)', 'outletpro' ) . '</span>',
			esc_html( $license_name )
		);
		return $links;
	}

	$formatted_expiry = wp_date( get_option( 'date_format' ), $license_expiry->getTimestamp() );

	switch ( $license_status ) {
		case 'expired':
			$license_message = sprintf(
				/* translators: 1: license name, 2: localized license expiry date. */
				'<span class="outletpro-license-expiry outletpro-alert-text">' . esc_html__( '%1$s expired %2$s', 'outletpro' ) . '</span>',
				esc_html( $license_name ),
				esc_html( $formatted_expiry )
			);
			break;
		default:
			$license_message = sprintf(
				/* translators: 1: license name, 2: localized license expiry date. */
				'<span class="outletpro-license-expiry">' . esc_html__( '%1$s until %2$s', 'outletpro' ) . '</span>',
				esc_html( $license_name ),
				esc_html( $formatted_expiry )
			);
	}

	$links[] = $license_message;

	return $links;
}
