<?php
/**
 * Plugin Name: Clearance Section for WooCommerce
 * Description: Move old stock easily by adding a dedicated clearance section to WooCommerce.
 * Version: 1.0.0
 * Author: Adrian Duffell
 * Author URI: https://adrianduffell.com
 * Text Domain: wc-clearance
 * License: GNU General Public License v3.0
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Requires Plugins: woocommerce
 * Requires at least: 6.9
 * Requires PHP: 7.4
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin version.
 *
 * @since 1.0.0
 */
const VERSION = '1.0.0';

require_once __DIR__ . '/includes/system-status.php';
require_once __DIR__ . '/includes/taxonomies.php';
require_once __DIR__ . '/includes/admin-product-options.php';
require_once __DIR__ . '/includes/admin-page-list-table.php';
require_once __DIR__ . '/includes/shortcodes.php';
require_once __DIR__ . '/includes/page.php';
require_once __DIR__ . '/includes/tools.php';
require_once __DIR__ . '/includes/setup-task.php';

/**
 * Initialize the plugin.
 *
 * Fired by `init`.
 *
 * @internal WordPress action hook
 */
function init_hook(): void {
	init_taxonomies();
	init_shortcodes();
	try {
		init_setup_task();
	} catch ( \Throwable $e ) {
		\wc_get_logger()->error( 'Could not initialize setup task: ' . $e->getMessage() );
	}
}

/**
 * Initialize the plugin’s wp-admin dashboard features.
 *
 * Fired by `admin_init`.
 *
 * @internal WordPress action hook
 */
function admin_init_hook(): void {
	init_admin_product_options();
	init_system_status();
	init_tools();
	init_admin_page_list_table();
}

/**
 * Plugin activation hook.
 */
function activate(): void {
	\wc_get_logger()->info( 'Activating Clearance Section for WooCommerce plugin.' );

	try {
		init_taxonomies(); // Needed since init hook does not run on activation.
		seed_clearance_status_taxonomy();
		create_clearance_page();
	} catch ( \RuntimeException $e ) {
		\wc_get_logger()->error( $e->getMessage() );
	}
}

/**
 * Enqueue admin-specific stylesheets.
 *
 * Fired by `admin_enqueue_scripts`.
 *
 * @internal WordPress action hook
 */
function enqueue_admin_styles_hook(): void {
	wp_enqueue_style(
		'wc-clearance-admin-styles',
		plugin_dir_url( __FILE__ ) . 'assets/css/admin.css',
		array(),
		VERSION
	);
}
add_action( 'admin_enqueue_scripts', 'WC_Clearance\enqueue_admin_styles_hook' );

/**
 * Enqueue the built JavaScript for the block editor.
 *
 * Fired by `enqueue_block_editor_assets`.
 *
 * @internal WordPress action hook
 */
function enqueue_build_js_hook(): void {
	$asset_file = plugin_dir_path( __FILE__ ) . 'build/index.asset.php';

	if ( ! file_exists( $asset_file ) ) {
		return;
	}

	$asset = require $asset_file;

	wp_enqueue_script(
		'wc-clearance-build',
		plugin_dir_url( __FILE__ ) . 'build/index.js',
		$asset['dependencies'],
		$asset['version'],
		true
	);

	try {
		$page_id = get_clearance_page_id();
	} catch ( \UnexpectedValueException $e ) {
		// Page ID is invalid; ignore.
		$page_id = null;
	}

	wp_add_inline_script(
		'wc-clearance-build',
		'window.wcClearance = ' . wp_json_encode( array( 'pageId' => $page_id ) ) . ';',
		'before'
	);
}
add_action( 'enqueue_block_editor_assets', 'WC_Clearance\enqueue_build_js_hook' );

// Hook into WordPress.
add_action( 'init', 'WC_Clearance\init_hook', 20 );
add_action( 'admin_init', 'WC_Clearance\admin_init_hook' );

register_activation_hook( __FILE__, __NAMESPACE__ . '\activate' );
