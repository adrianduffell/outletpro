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

/**
 * Initialize the plugin
 */
function init(): void {
	init_taxonomies();
}

/**
 * Plugin activation hook.
 */
function activate(): void {
	\wc_get_logger()->info( 'Activating Clearance Section for WooCommerce plugin.' );

	init_taxonomies(); // Needed since init hook does not run on activation.
	try {
		seed_clearance_status_taxonomy();
	} catch ( \RuntimeException $e ) {
		\wc_get_logger()->error( $e->getMessage() );
	}
}

/**
 * Enqueue admin-specific stylesheets.
 *
 * @param string $hook_suffix The hook suffix for the current admin page (e.g. 'woocommerce_page_wc-status').
 */
function enqueue_admin_styles( string $hook_suffix ): void {
	$allowed_screens = array(
		'woocommerce_page_wc-status', // WooCommerce system status page.
		'woocommerce_page_wc-admin',  // WooCommerce block product editor.
	);
	if ( ! in_array( $hook_suffix, $allowed_screens, true ) ) {
		return;
	}
	wp_enqueue_style(
		'wc-clearance-admin-styles',
		plugin_dir_url( __FILE__ ) . 'assets/css/admin.css',
		array(),
		VERSION
	);
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_admin_styles' );

// Hook into WordPress.
add_action( 'woocommerce_init', __NAMESPACE__ . '\init' );
if ( is_admin() ) {
	add_action( 'woocommerce_system_status_report', __NAMESPACE__ . '\add_system_status_section', 99 );
}
register_activation_hook( __FILE__, __NAMESPACE__ . '\activate' );
