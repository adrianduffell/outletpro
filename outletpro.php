<?php
/**
 * Plugin Name: Outlet Pro
 * Description: Move old stock easily with an outlet on WooCommerce stores.
 * Version: 1.0.1
 * Author: Adrian Duffell
 * Author URI: https://adrianduffell.com
 * Text Domain: outletpro
 * License: GNU General Public License v3.0
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Requires Plugins: woocommerce
 * Requires at least: 6.9
 * Requires PHP: 7.4
 * Update URI: https://adrianduffell.store/outletpro
 *
 * WC requires at least: 10.7
 * WC tested up to: 10.8
 *
 * @package OutletPro
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin version.
 *
 * @internal
 */
const VERSION = '1.0.1';

/**
 * Plugin file path.
 *
 * @internal
 */
const PLUGIN_FILE = __FILE__;

require_once __DIR__ . '/includes/activate.php';
require_once __DIR__ . '/includes/system-status.php';
require_once __DIR__ . '/includes/taxonomies.php';
require_once __DIR__ . '/includes/rest-api.php';
// #ifdef LICENSE
require_once __DIR__ . '/includes/admin-menu.php';
// #endif
require_once __DIR__ . '/includes/admin-product-options.php';
require_once __DIR__ . '/includes/admin-product-bulk-edit.php';
require_once __DIR__ . '/includes/admin-page-list-table.php';
require_once __DIR__ . '/includes/shortcodes.php';
require_once __DIR__ . '/includes/settings.php';
// #ifdef LICENSE
require_once __DIR__ . '/includes/license.php';
require_once __DIR__ . '/includes/enqueue-license.php';
// #endif
require_once __DIR__ . '/includes/patterns.php';
require_once __DIR__ . '/includes/page.php';
require_once __DIR__ . '/includes/tools.php';
require_once __DIR__ . '/includes/setup-task.php';
require_once __DIR__ . '/includes/admin-product-list-table.php';
require_once __DIR__ . '/includes/block-editor.php';
require_once __DIR__ . '/includes/blocks.php';
require_once __DIR__ . '/includes/admin-order.php';
require_once __DIR__ . '/includes/cart.php';
require_once __DIR__ . '/includes/orders.php';
require_once __DIR__ . '/includes/customizer.php';
require_once __DIR__ . '/includes/woocommerce-template-hooks.php';
require_once __DIR__ . '/includes/enqueue.php';
// #ifdef UPDATES
require_once __DIR__ . '/includes/update-plugin.php';
// #endif

/**
 * Initialize the plugin.
 *
 * Fired by `init`.
 *
 * @internal WordPress action hook
 */
function init_hook(): void {
	// #ifdef UPDATES
	init_update_plugin();
	// #endif
	init_settings();
	init_taxonomies();
	init_rest_api();
	init_shortcodes();
	init_patterns();
	init_page();
	init_blocks();
	init_block_editor();
	init_orders();
	init_cart();

	// #ifdef LICENSE
	if ( is_admin() ) {
		// The admin menu hooks need to run before admin_init.
		init_admin_menu();
	}
	// #endif
	if ( ! wp_is_block_theme() ) {
		init_customizer();
		init_woocommerce_template_hooks();
	}
	enqueue_init();
	// #ifdef LICENSE
	license_enqueue_init();
	// #endif
	try {
		init_setup_task();
	} catch ( \Throwable $e ) {
		\wc_get_logger()->error( 'Could not initialize setup task: ' . $e->getMessage() );
	}
}

/**
 * Initialize the plugin's wp-admin dashboard features.
 *
 * Fired by `admin_init`.
 *
 * @internal WordPress action hook
 */
function admin_init_hook(): void {
	// #ifdef LICENSE
	init_license();
	// #endif
	init_admin_product_options();
	init_admin_product_bulk_edit();
	init_system_status();
	init_tools();
	init_admin_page_list_table();
	init_admin_product_list_table();
	init_admin_order();
}

/**
 * Register the plugin's init hooks once WooCommerce is active.
 *
 * Fired by `woocommerce_loaded`.
 *
 * @internal WordPress action hook
 */
function woocommerce_loaded_hook(): void {
	add_action( 'init', 'OutletPro\init_hook', 20 );
	add_action( 'admin_init', 'OutletPro\admin_init_hook' );
}

add_action( 'woocommerce_loaded', 'OutletPro\woocommerce_loaded_hook' );

/**
 * Plugin activation hook.
 *
 * @internal
 */
function activate(): void {
	\wc_get_logger()->info( 'Activating Outlet plugin.' );

	try {
		init_taxonomies(); // Needed since init hook does not run on activation.
		init_patterns(); // Needed to create the outlet page.
		seed_outlet_status_taxonomy();
		create_outlet_page();
		seed_activated_at_option();
		seed_settings();
	} catch ( \RuntimeException $e ) {
		\wc_get_logger()->error( $e->getMessage() );
	}
}

register_activation_hook( __FILE__, __NAMESPACE__ . '\activate' );

add_action(
	'before_woocommerce_init',
	function (): void {
		if ( ! class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			return;
		}
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', PLUGIN_FILE, true );
	}
);
