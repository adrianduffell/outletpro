<?php
/**
 * Enqueue functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize enqueue registrations.
 *
 * @internal
 */
function enqueue_init(): void {
	add_action( 'wp_enqueue_scripts', 'WC_Clearance\register_classic_styles_hook' );
	add_action( 'wp_enqueue_scripts', 'WC_Clearance\enqueue_block_styles_hook' );
	add_action( 'admin_enqueue_scripts', 'WC_Clearance\enqueue_admin_styles_hook' );
	add_action( 'admin_enqueue_scripts', 'WC_Clearance\enqueue_admin_product_scripts_hook' );
	add_action( 'admin_enqueue_scripts', 'WC_Clearance\enqueue_block_styles_hook' );
	add_action( 'enqueue_block_editor_assets', 'WC_Clearance\enqueue_build_assets_hook' );
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
		plugin_dir_url( PLUGIN_FILE ) . 'assets/css/classic.css',
		array(),
		VERSION
	);
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
		plugin_dir_url( PLUGIN_FILE ) . 'assets/css/admin.css',
		array(),
		VERSION
	);
}

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
		plugin_dir_url( PLUGIN_FILE ) . 'assets/js/admin-product.js',
		array(),
		VERSION,
		true
	);
}

/**
 * Enqueue the built block CSS for both public and admin sites.
 *
 * Fired by `wp_enqueue_scripts` and `admin_enqueue_scripts`.
 *
 * @internal WordPress action hook
 */
function enqueue_block_styles_hook(): void {
	$style_file = plugin_dir_path( PLUGIN_FILE ) . 'build/style-index.css';

	if ( ! file_exists( $style_file ) ) {
		return;
	}

	wp_enqueue_style(
		'wc-clearance-block-styles',
		plugin_dir_url( PLUGIN_FILE ) . 'build/style-index.css',
		array(),
		VERSION
	);
}

/**
 * Enqueue the built JavaScript for the block editor.
 *
 * Fired by `enqueue_block_editor_assets`.
 *
 * @internal WordPress action hook
 */
function enqueue_build_assets_hook(): void {
	$asset_file = plugin_dir_path( PLUGIN_FILE ) . 'build/index.asset.php';

	if ( ! file_exists( $asset_file ) ) {
		return;
	}

	$asset = require $asset_file;

	wp_enqueue_script(
		'wc-clearance-build',
		plugin_dir_url( PLUGIN_FILE ) . 'build/index.js',
		array_merge( $asset['dependencies'], array( 'wc-blocks-registry' ) ),
		$asset['version'],
		true
	);
}
