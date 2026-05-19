<?php
/**
 * Enqueue functions.
 *
 * @package WC_Outlet
 */

namespace WC_Outlet;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize enqueue registrations.
 *
 * @internal
 */
function enqueue_init(): void {
	add_action( 'wp_enqueue_scripts', 'WC_Outlet\register_classic_styles_hook' );
	add_action( 'wp_enqueue_scripts', 'WC_Outlet\enqueue_cart_styles_hook' );
	add_action( 'enqueue_block_assets', 'WC_Outlet\enqueue_admin_editor_styles_hook' );
	add_action( 'enqueue_block_assets', 'WC_Outlet\enqueue_admin_canvas_scripts_hook' );
	add_action( 'wp_head', 'WC_Outlet\output_badge_style_css_variables_hook' );
	add_action( 'admin_enqueue_scripts', 'WC_Outlet\enqueue_admin_styles_hook' );
	add_action( 'admin_enqueue_scripts', 'WC_Outlet\enqueue_admin_product_scripts_hook' );
	add_action( 'enqueue_block_editor_assets', 'WC_Outlet\enqueue_build_assets_hook' );

	register_block_styles();
}

/**
 * Helper to de-initialize enqueue registrations back to the uninitialized state.
 *
 * @internal
 */
function deinit_enqueue(): void {
	remove_action( 'wp_enqueue_scripts', 'WC_Outlet\enqueue_cart_styles_hook' );
	remove_action( 'wp_enqueue_scripts', 'WC_Outlet\register_classic_styles_hook' );
	remove_action( 'enqueue_block_assets', 'WC_Outlet\enqueue_admin_editor_styles_hook' );
	remove_action( 'enqueue_block_assets', 'WC_Outlet\enqueue_admin_canvas_scripts_hook' );
	remove_action( 'wp_head', 'WC_Outlet\output_badge_style_css_variables_hook' );
	remove_action( 'admin_enqueue_scripts', 'WC_Outlet\enqueue_admin_styles_hook' );
	remove_action( 'admin_enqueue_scripts', 'WC_Outlet\enqueue_admin_product_scripts_hook' );
	remove_action( 'enqueue_block_editor_assets', 'WC_Outlet\enqueue_build_assets_hook' );
	wp_deregister_style( 'wc-outlet-classic-badge' );
	wp_deregister_style( 'wc-outlet-classic-message' );
	wp_dequeue_style( 'wc-outlet-cart-badge' );
	wp_deregister_style( 'wc-outlet-cart-badge' );
	wp_dequeue_style( 'wc-outlet-admin' );
	wp_deregister_style( 'wc-outlet-admin' );
	wp_dequeue_style( 'wc-outlet-admin-editor' );
	wp_deregister_style( 'wc-outlet-admin-editor' );
	wp_dequeue_script( 'wc-outlet-admin-canvas-scripts' );
	wp_deregister_script( 'wc-outlet-admin-canvas-scripts' );
	wp_dequeue_script( 'wc-outlet-products-admin' );
	wp_deregister_script( 'wc-outlet-products-admin' );
	wp_dequeue_script( 'wc-outlet-editor' );
	wp_deregister_script( 'wc-outlet-editor' );
	wp_dequeue_style( 'wc-outlet-badge-block' );
	wp_deregister_style( 'wc-outlet-badge-block' );
}

/**
 * Enqueue admin canvas scripts.
 *
 * Fired by `enqueue_block_assets`.
 *
 * @internal WordPress action hook
 */
function enqueue_admin_canvas_scripts_hook(): void {
	if ( ! is_admin() ) {
		return;
	}

	/**
	 * Admin canvas scripts.
	 *
	 * @internal
	 */
	wp_enqueue_script(
		'wc-outlet-admin-canvas-scripts',
		plugin_dir_url( PLUGIN_FILE ) . 'assets/js/admin-canvas.js',
		array(),
		VERSION,
		false
	);
}

/**
 * Enqueue admin editor styles for previewing cart and checkout badge placement in the editor canvas.
 *
 * Fired by `enqueue_block_assets`.
 *
 * @internal WordPress action hook
 */
function enqueue_admin_editor_styles_hook(): void {
	if ( ! is_admin() ) {
		return;
	}

	/**
	 * Admin editor stylesheet.
	 *
	 * @internal
	 */
	wp_enqueue_style(
		'wc-outlet-admin-editor',
		plugin_dir_url( PLUGIN_FILE ) . 'assets/css/admin-editor.css',
		array(),
		VERSION
	);
}

/**
 * Register classic theme front-end stylesheets.
 *
 * Fired by `wp_enqueue_scripts`.
 *
 * @internal WordPress action hook
 */
function register_classic_styles_hook(): void {
	/**
	 * Classic theme front-end badge stylesheet.
	 *
	 * @since 1.0.0
	 */
	wp_register_style(
		'wc-outlet-classic-badge',
		plugin_dir_url( PLUGIN_FILE ) . 'assets/css/classic-badge.css',
		array(),
		VERSION
	);

	/**
	 * Classic theme front-end message stylesheet.
	 *
	 * @since 1.0.0
	 */
	wp_register_style(
		'wc-outlet-classic-message',
		plugin_dir_url( PLUGIN_FILE ) . 'assets/css/classic-message.css',
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
	$label = sanitize_text_field( get_option( OUTLET_BADGE_LABEL_OPTION, '' ) );

	/**
	 * Front-end cart badge stylesheet.
	 *
	 * @internal
	 */
	wp_register_style(
		'wc-outlet-cart-badge',
		plugin_dir_url( PLUGIN_FILE ) . 'assets/css/cart.css',
		array(),
		VERSION
	);

	wp_enqueue_style( 'wc-outlet-cart-badge' );

	wp_add_inline_style(
		'wc-outlet-cart-badge',
		':root { --wc-outlet-badge-label: ' . ( '' !== $label ? wp_json_encode( $label, JSON_UNESCAPED_UNICODE ) : 'none' ) . '; }'
	);
}

/**
 * Output badge style settings as CSS variables on front-end pages.
 *
 * Fired by `wp_head`.
 *
 * @internal WordPress action hook
 */
function output_badge_style_css_variables_hook(): void {
	$badge_style_options = array(
		OUTLET_BADGE_BG_COLOR_OPTION,
		OUTLET_BADGE_TEXT_COLOR_OPTION,
		OUTLET_BADGE_BORDER_COLOR_OPTION,
		OUTLET_BADGE_BORDER_STYLE_OPTION,
		OUTLET_BADGE_BORDER_WIDTH_OPTION,
		OUTLET_BADGE_BORDER_RADIUS_OPTION,
		OUTLET_BADGE_FONT_WEIGHT_OPTION,
		OUTLET_BADGE_SCALE_OPTION,
		OUTLET_BADGE_DENSITY_OPTION,
	);

	$declarations = array_map(
		function ( string $option_name ): string {
			$variable_name = '--' . str_replace( '_', '-', $option_name );
			$option_value  = sanitize_css_value( get_option( $option_name, '' ) );

			return $variable_name . ': ' . ( '' !== $option_value ? $option_value : 'unset' );
		},
		$badge_style_options
	);

	echo '<style id="wc-outlet-badge-vars">:root { ' . esc_html( implode( '; ', $declarations ) ) . '; }</style>';
}

/**
 * Register the block stylesheet so it only loads when the outlet badge block is rendered.
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

	/**
	 * Block stylesheet for the outlet badge block.
	 *
	 * @internal
	 */
	wp_enqueue_block_style(
		'wc-outlet/outlet-badge',
		array(
			'handle' => 'wc-outlet-badge-block',
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
	/**
	 * Admin stylesheet.
	 *
	 * @internal
	 */
	wp_enqueue_style(
		'wc-outlet-admin',
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

	/**
	 * Admin product edit page script.
	 *
	 * @internal
	 */
	wp_enqueue_script(
		'wc-outlet-products-admin',
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

	/**
	 * Block editor script.
	 *
	 * @internal
	 */
	wp_enqueue_script(
		'wc-outlet-editor',
		plugin_dir_url( PLUGIN_FILE ) . 'build/index.js',
		array_merge( $asset['dependencies'], array( 'wc-blocks-registry' ) ),
		$asset['version'],
		true
	);
}
