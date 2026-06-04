<?php
/**
 * Tools functions.
 *
 * @package WC_Outlet
 */

namespace WC_Outlet;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize tools.
 *
 * @internal
 */
function init_tools(): void {
	add_filter( 'woocommerce_debug_tools', 'WC_Outlet\register_create_outlet_page_tool_hook' );
}

/**
 * Register the create outlet page tool in WooCommerce > Status > Tools.
 *
 * Fired by `woocommerce_debug_tools`.
 *
 * @param array<string, mixed> $tools Existing tools.
 * @return array<string, mixed> Modified tools.
 * @internal WordPress filter
 */
function register_create_outlet_page_tool_hook( array $tools ): array {
	$tools['create_outlet_page'] = array(
		'name'     => __( 'Create outlet page', 'outletpro' ),
		'button'   => __( 'Create page', 'outletpro' ),
		'desc'     => __( 'Creates a draft page with the outlet products shortcode.', 'outletpro' ),
		'callback' => __NAMESPACE__ . '\run_create_outlet_page_tool',
	);

	return $tools;
}

/**
 * WooCommerce Status > Tools callback for the create outlet page tool.
 *
 * @internal
 */
function run_create_outlet_page_tool(): string {
	try {
		if ( outlet_page_exists() ) {
			return __( 'Outlet page already exists.', 'outletpro' );
		}
		create_outlet_page();
	} catch ( \Throwable $e ) {
		return __( 'Outlet page could not be created.', 'outletpro' );
	}

	return __( 'Outlet page created.', 'outletpro' );
}
