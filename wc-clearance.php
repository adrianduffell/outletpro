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

require_once __DIR__ . '/includes/taxonomies.php';

/**
 * Initialize the plugin
 */
function init(): void {
	init_taxonomies();
}


// Hook into WordPress.
add_action( 'woocommerce_init', __NAMESPACE__ . '\init' );
