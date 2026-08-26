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
	add_filter( 'plugin_row_meta', 'OutletPro\add_plugin_meta_links_hook', 10, 2 );
}

/**
 * Helper to de-initialize license features back to the uninitialized state.
 *
 * @internal
 */
function deinit_license(): void {
	remove_filter( 'plugin_action_links_' . plugin_basename( PLUGIN_FILE ), 'OutletPro\add_plugin_action_links_hook' );
	remove_filter( 'plugin_row_meta', 'OutletPro\add_plugin_meta_links_hook' );
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
