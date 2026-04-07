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

require_once __DIR__ . '/includes/activate.php';
require_once __DIR__ . '/includes/system-status.php';
require_once __DIR__ . '/includes/taxonomies.php';
require_once __DIR__ . '/includes/rest-api.php';
require_once __DIR__ . '/includes/admin-product-options.php';
require_once __DIR__ . '/includes/admin-page-list-table.php';
require_once __DIR__ . '/includes/shortcodes.php';
require_once __DIR__ . '/includes/settings.php';
require_once __DIR__ . '/includes/page.php';
require_once __DIR__ . '/includes/tools.php';
require_once __DIR__ . '/includes/setup-task.php';
require_once __DIR__ . '/includes/admin-product-list-table.php';
require_once __DIR__ . '/includes/block-editor.php';
require_once __DIR__ . '/includes/blocks.php';
require_once __DIR__ . '/includes/customizer.php';
require_once __DIR__ . '/includes/woocommerce-template-hooks.php';

/**
 * Initialize the plugin.
 *
 * Fired by `init`.
 *
 * @internal WordPress action hook
 */
function init_hook(): void {
	init_settings();
	init_taxonomies();
	init_rest_api();
	init_shortcodes();
	blocks_init();
	block_editor_init();
	if ( ! wp_is_block_theme() ) {
		init_customizer();
	}
	init_woocommerce_template_hooks();
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
	init_admin_product_list_table();
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
		seed_activated_at_option();
	} catch ( \RuntimeException $e ) {
		\wc_get_logger()->error( $e->getMessage() );
	}
}

/**
 * Register classic theme front-end stylesheets.
 *
 * Fired by `wp_enqueue_scripts`.
 *
 * @internal WordPress action hook
 */
function register_classic_styles_hook(): void {
	wp_register_style(
		'wc-clearance',
		plugin_dir_url( __FILE__ ) . 'assets/css/classic.css',
		array(),
		VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'WC_Clearance\register_classic_styles_hook' );

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
 * Enqueue admin scripts for the product edit page.
 *
 * Fired by `admin_enqueue_scripts`.
 *
 * @internal WordPress action hook
 */
function enqueue_admin_product_scripts_hook(): void {
	$screen = get_current_screen();

	if ( ! $screen || 'product' !== $screen->post_type || 'post' !== $screen->base ) {
		return;
	}

	wp_enqueue_script(
		'wc-clearance-admin-product',
		plugin_dir_url( __FILE__ ) . 'assets/js/admin-product.js',
		array(),
		VERSION,
		true
	);
}
add_action( 'admin_enqueue_scripts', 'WC_Clearance\enqueue_admin_product_scripts_hook' );

/**
 * Enqueue the built JavaScript for the block editor.
 *
 * Fired by `enqueue_block_editor_assets`.
 *
 * @internal WordPress action hook
 */
function enqueue_build_assets_hook(): void {
	$canonical_term = get_term_by( 'name', CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );
	$asset_file     = plugin_dir_path( __FILE__ ) . 'build/index.asset.php';

	if ( ! file_exists( $asset_file ) ) {
		return;
	}

	$asset = require $asset_file;

	wp_enqueue_script(
		'wc-clearance-build',
		plugin_dir_url( __FILE__ ) . 'build/index.js',
		array_merge( $asset['dependencies'], array( 'wc-blocks-registry' ) ),
		$asset['version'],
		true
	);

	wp_localize_script(
		'wc-clearance-build',
		'wcClearance',
		array(
			'clearanceTermId' => $canonical_term->term_id,
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'WC_Clearance\enqueue_build_assets_hook' );

/**
 * Enqueue the checkout block slot fill script on frontend pages.
 *
 * Fired by `wp_enqueue_scripts`.
 *
 * @internal WordPress action hook
 */
function enqueue_checkout_fill_hook(): void {
	wp_enqueue_script(
		'wc-clearance-checkout-fill',
		plugin_dir_url( __FILE__ ) . 'assets/js/checkout.js',
		array( 'wp-plugins', 'wp-element', 'wp-data' ),
		VERSION,
		true
	);

	wp_enqueue_style( 'wp-block-wc-clearance-clearance-badge' );
}
add_action( 'wp_enqueue_scripts', 'WC_Clearance\enqueue_checkout_fill_hook', 20 );

// Hook into WordPress.
add_action( 'init', 'WC_Clearance\init_hook', 20 );
add_action( 'admin_init', 'WC_Clearance\admin_init_hook' );

register_activation_hook( __FILE__, __NAMESPACE__ . '\activate' );
