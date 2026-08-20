<?php
/**
 * Admin menu functions for the license settings page.
 *
 * @package OutletPro
 * @subpackage License
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Admin page slug for the welcome page.
 *
 * @internal
 */
const WELCOME_PAGE_SLUG = 'outletpro-welcome';

/**
 * Cookie used to dismiss the welcome screen on the current device.
 *
 * @internal
 */
const DISMISS_COOKIE = 'OUTLETPRO_DISMISS_SETUP';

/**
 * Helper to initialize license features.
 *
 * @internal
 */
function init_admin_menu(): void {
	add_action( 'admin_menu', 'OutletPro\add_license_menu_hook' );

	$direct = isset( $_GET['page'] ) && 'outletpro-welcome' === wp_unslash( $_GET['page'] ); // phpcs:ignore

	if ( isset( $_COOKIE[ DISMISS_COOKIE ] ) && ! $direct ) {
		return;
	}

	$license_status = get_license_status();

	if ( in_array( $license_status, array( 'active', 'error' ), true ) && ! $direct ) {
		return;
	}

	add_action( 'admin_menu', 'OutletPro\add_welcome_menu_hook' );
}

/**
 * Helper to de-initialize license features back to the uninitialized state.
 *
 * @internal
 */
function deinit_admin_menu(): void {
	remove_action( 'admin_menu', 'OutletPro\add_license_menu_hook' );
	remove_action( 'admin_menu', 'OutletPro\add_welcome_menu_hook' );
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
		LICENSE_OPTIONS_GROUP,
		'OutletPro\render_license_page'
	);
}

/**
 * Register the welcome admin page when no valid license is active.
 *
 * Fired by `admin_menu`.
 *
 * @internal WordPress action hook
 */
function add_welcome_menu_hook(): void {
	add_menu_page(
		__( 'Welcome to Outlet Pro', 'outletpro' ),
		__( 'Outlet Pro Setup', 'outletpro' ),
		'manage_options',
		WELCOME_PAGE_SLUG,
		'OutletPro\render_welcome_page',
		'dashicons-admin-generic',
		0
	);
}

/**
 * Render the welcome page.
 *
 * @internal
 */
function render_welcome_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<div id="outletpro-welcome-page-root"></div>
	</div>
	<?php
}
