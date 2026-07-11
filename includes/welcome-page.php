<?php
/**
 * Welcome page functions.
 *
 * @package OutletPro
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
 * Helper to initialize welcome page features.
 *
 * @internal
 */
function init_welcome_page(): void {
	add_action( 'admin_menu', 'OutletPro\add_welcome_menu_hook' );
}

/**
 * Helper to de-initialize welcome page features back to the uninitialized state.
 *
 * @internal
 */
function deinit_welcome_page(): void {
	remove_action( 'admin_menu', 'OutletPro\add_welcome_menu_hook' );
}

/**
 * Register the welcome admin page when no valid license is active.
 *
 * Fired by `admin_menu`.
 *
 * @internal WordPress action hook
 */
function add_welcome_menu_hook(): void {
	if ( has_license() ) {
		return;
	}

	add_menu_page(
		__( 'Welcome to Outlet Pro', 'outletpro' ),
		__( 'Set up Outlet Pro', 'outletpro' ),
		'manage_options',
		WELCOME_PAGE_SLUG,
		'OutletPro\render_welcome_page'
	);

	add_action( 'admin_enqueue_scripts', 'OutletPro\enqueue_welcome_page_scripts_hook' );
}

/**
 * Enqueue scripts for the welcome page.
 *
 * Fired by `admin_enqueue_scripts`.
 *
 * @internal WordPress action hook
 */
function enqueue_welcome_page_scripts_hook(): void {
	$screen = get_current_screen();

	if ( ! $screen || 'toplevel_page_' . WELCOME_PAGE_SLUG !== $screen->id ) {
		return;
	}

	$asset_file = plugin_dir_path( PLUGIN_FILE ) . 'build/welcome-page.asset.php';

	if ( ! file_exists( $asset_file ) ) {
		return;
	}

	$asset = require $asset_file;

	/**
	 * Welcome page script.
	 *
	 * @internal
	 */
	wp_enqueue_script(
		'outletpro-welcome-page',
		plugin_dir_url( PLUGIN_FILE ) . 'build/welcome-page.js',
		$asset['dependencies'],
		$asset['version'],
		true
	);

	wp_localize_script(
		'outletpro-welcome-page',
		'outletproWelcomePage',
		array(
			'licenseKey'  => (string) get_option( LICENSE_KEY_OPTION, '' ),
			'productsUrl' => esc_url( admin_url( 'edit.php?post_type=product' ) ),
		)
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
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<div id="outletpro-welcome-page"></div>
	</div>
	<?php
}
