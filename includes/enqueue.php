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
	add_action( 'wp_enqueue_scripts', 'WC_Clearance\enqueue_cart_styles_hook' );
	add_action( 'admin_enqueue_scripts', 'WC_Clearance\enqueue_admin_styles_hook' );
	add_action( 'admin_enqueue_scripts', 'WC_Clearance\enqueue_admin_product_scripts_hook' );
	add_action( 'enqueue_block_editor_assets', 'WC_Clearance\enqueue_build_assets_hook' );

	register_block_styles();
}

/**
 * Helper to de-initialize enqueue registrations back to the uninitialized state.
 *
 * @internal
 */
function deinit_enqueue(): void {
	remove_action( 'wp_enqueue_scripts', 'WC_Clearance\enqueue_cart_styles_hook' );
	remove_action( 'wp_enqueue_scripts', 'WC_Clearance\register_classic_styles_hook' );
	remove_action( 'admin_enqueue_scripts', 'WC_Clearance\enqueue_admin_styles_hook' );
	remove_action( 'admin_enqueue_scripts', 'WC_Clearance\enqueue_admin_product_scripts_hook' );
	remove_action( 'enqueue_block_editor_assets', 'WC_Clearance\enqueue_build_assets_hook' );
	wp_dequeue_style( 'wc-clearance-block-styles' );
	wp_deregister_style( 'wc-clearance-block-styles' );
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
 * Enqueue front-end cart stylesheet.
 *
 * Fired by `wp_enqueue_scripts`.
 *
 * @internal WordPress action hook
 */
function enqueue_cart_styles_hook(): void {
	$bg_color   = sanitize_hex_color( get_option( CLEARANCE_BADGE_BG_COLOR_OPTION, '#FFEE85' ) );
	$text_color = sanitize_hex_color( get_option( CLEARANCE_BADGE_TEXT_COLOR_OPTION, '#222' ) );
	$label = sanitize_text_field( get_option( CLEARANCE_BADGE_LABEL_OPTION, __( 'Clearance', 'wc-clearance' ) ) );

	wp_register_style(
		'wc-clearance-cart',
		plugin_dir_url( PLUGIN_FILE ) . 'assets/css/cart.css',
		array(),
		VERSION
	);

	wp_enqueue_style( 'wc-clearance-cart' );

	wp_add_inline_style(
		'wc-clearance-cart',
		':root { --wc-clearance-badge-bg-color: ' . $bg_color . '; --wc-clearance-badge-text-color: ' . $text_color . '; --wc-clearance-badge-label: ' . wp_json_encode( $label, JSON_UNESCAPED_UNICODE ) . '; }'
	);
}

/**
 * Register the block stylesheet so it only loads when the clearance badge block is rendered.
 *
 * The message block doesn't have any styles currently.
 *
 * @internal
 */
function register_block_styles(): void {
	$asset_file = plugin_dir_path( PLUGIN_FILE ) . 'build/index.asset.php';

	if ( ! file_exists( $asset_file ) ) {
		return;
	}

	$asset = require $asset_file;

	wp_enqueue_block_style(
		'wc-clearance/clearance-badge',
		array(
			'handle' => 'wc-clearance-block-styles',
			'src'    => plugin_dir_url( PLUGIN_FILE ) . 'build/style-index.css',
			'deps'   => array(),
			'ver'    => $asset['version'],
		)
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
