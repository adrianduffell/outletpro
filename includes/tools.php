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
	add_filter( 'woocommerce_debug_tools', __NAMESPACE__ . '\register_create_clearance_page_tool' );
}

/**
 * Register the create clearance page tool in WooCommerce > Status > Tools.
 *
 * @param array<string, mixed> $tools Existing tools.
 * @return array<string, mixed> Modified tools.
 * @since 1.0.0
 */
function register_create_clearance_page_tool( array $tools ): array {
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
	if ( clearance_page_exists() ) {
		return __( 'Clearance section page already exists.', 'wc-clearance' );
	}

	try {
		create_clearance_page();
	} catch ( \RuntimeException $e ) {
		return __( 'Clearance section page could not be created.', 'wc-clearance' );
	}

	return __( 'Clearance section page created.', 'wc-clearance' );
}
