<?php
/**
 * License functions.
 *
 * @package OutletPro
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Admin page slug for the license settings page.
 *
 * @internal
 */
const LICENSE_PAGE_SLUG = 'outletpro-license';

/**
 * Helper to initialize license features.
 *
 * @internal
 */
function init_admin_menu(): void {
	add_action( 'admin_menu', 'OutletPro\add_license_menu_hook' );
}

/**
 * Helper to de-initialize license features back to the uninitialized state.
 *
 * @internal
 */
function deinit_admin_menu(): void {
	remove_action( 'admin_menu', 'OutletPro\add_license_menu_hook' );
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
