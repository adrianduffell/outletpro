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
		\wc_get_logger()->error( 'Premium license status could not be checked' );
		return;
	}
	?>
	<tr class="plugin-update-tr active outletpro-license-notice">
		<td colspan="4" class="plugin-update colspanchange">
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
