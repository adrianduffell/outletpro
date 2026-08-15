<?php
/**
 * License enqueue functions.
 *
 * @package OutletPro
 * @subpackage License
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize license enqueue registrations.
 *
 * @internal
 */
function license_enqueue_init(): void {
	add_action( 'admin_enqueue_scripts', 'OutletPro\enqueue_admin_welcome_page_scripts_hook' );
}

/**
 * Enqueue admin scripts for the welcome/setup page.
 *
 * Fired by `admin_enqueue_scripts`.
 *
 * @internal WordPress action hook
 */
function enqueue_admin_welcome_page_scripts_hook(): void {
	$screen = get_current_screen();

	if ( ! $screen || 'toplevel_page_' . WELCOME_PAGE_SLUG !== $screen->id ) {
		return;
	}

	$asset_file = plugin_dir_path( PLUGIN_FILE ) . 'build/index.asset.php';

	if ( ! file_exists( $asset_file ) ) {
		return;
	}

	$asset = require $asset_file;

	/**
	 * Admin welcome page script.
	 *
	 * @internal
	 */
	wp_enqueue_script(
		'outletpro-welcome-page',
		plugin_dir_url( PLUGIN_FILE ) . 'build/index.js',
		$asset['dependencies'],
		$asset['version'],
		true
	);

	try {
		$hostname = get_hostname();
	} catch ( \RuntimeException $e ) {
		\wc_get_logger()->error( 'Hostname could not be retrieved.' );
		$hostname = '';
	}

	wp_localize_script(
		'outletpro-welcome-page',
		'outletproWelcomePage',
		array(
			'environmentType' => wp_get_environment_type(),
			'hostname'        => $hostname,
			'isLocalHost'     => is_local_env(),
			'licenseKey'      => (string) get_option( LICENSE_KEY_OPTION, '' ),
			'productsUrl'     => esc_url( admin_url( 'edit.php?post_type=product' ) ),
		)
	);

	/**
	 * Admin stylesheet.
	 *
	 * @internal
	 */
	wp_enqueue_style(
		'outletpro-welcome-page-style',
		plugin_dir_url( PLUGIN_FILE ) . 'build/style-index.css',
		array(),
		$asset['version']
	);
}
