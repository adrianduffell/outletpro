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
require_once __DIR__ . '/includes/shortcodes.php';

/**
 * Initialize the plugin.
 */
function init(): void {
	init_taxonomies();
}

/**
 * Initialize the plugin’s wp-admin dashboard features.
 */
function admin_init(): void {
	init_admin_product_options();
	init_system_status();
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
 */
function enqueue_admin_styles(): void {
	wp_enqueue_style(
		'wc-clearance-admin-styles',
		plugin_dir_url( __FILE__ ) . 'assets/css/admin.css',
		array(),
		VERSION
	);
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_admin_styles' );

// Hook into WordPress.
add_action( 'init', __NAMESPACE__ . '\init' );
add_action( 'admin_init', __NAMESPACE__ . '\admin_init' );

register_activation_hook( __FILE__, __NAMESPACE__ . '\activate' );
