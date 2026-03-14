<?php
/**
 * Tools functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize tools.
 *
 * @since 1.0.0
 */
function init_tools(): void {
	add_filter( 'woocommerce_debug_tools', 'WC_Clearance\register_create_clearance_page_tool_hook' );
}

/**
 * Register the create clearance page tool in WooCommerce > Status > Tools.
 *
 * Fired by `woocommerce_debug_tools`.
 *
 * @param array<string, mixed> $tools Existing tools.
 * @return array<string, mixed> Modified tools.
 * @internal WordPress filter
 */
function register_create_clearance_page_tool_hook( array $tools ): array {
	$tools['create_clearance_page'] = array(
		'name'     => __( 'Create clearance section page', 'wc-clearance' ),
		'button'   => __( 'Create page', 'wc-clearance' ),
		'desc'     => __( 'Creates a draft page with the clearance products shortcode.', 'wc-clearance' ),
		'callback' => __NAMESPACE__ . '\run_create_clearance_page_tool',
	);

	return $tools;
}

/**
 * WooCommerce Status > Tools callback for the create clearance section page tool.
 *
 * @since 1.0.0
 */
function run_create_clearance_page_tool(): string {
	try {
		if ( clearance_page_exists() ) {
			return __( 'Clearance section page already exists.', 'wc-clearance' );
		}
		create_clearance_page();
	} catch ( \Throwable $e ) {
		return __( 'Clearance section page could not be created.', 'wc-clearance' );
	}

	return __( 'Clearance section page created.', 'wc-clearance' );
}
