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

// Hook into WordPress.
add_action( 'woocommerce_init', __NAMESPACE__ . '\init' );
if ( is_admin() ) {
	add_action( 'woocommerce_system_status_report', __NAMESPACE__ . '\add_system_status_section', 99 );
}
register_activation_hook( __FILE__, __NAMESPACE__ . '\activate' );
