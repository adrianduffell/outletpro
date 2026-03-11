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
		'callback' => __NAMESPACE__ . '\create_clearance_page',
	);

	return $tools;
}

/**
 * Create the clearance section page.
 *
 * @since 1.0.0
 */
function create_clearance_page(): string {
	$page_id = wp_insert_post(
		array(
			'post_title'   => __( 'Clearance', 'wc-clearance' ),
			'post_name'    => 'clearance',
			'post_status'  => 'draft',
			'post_content' => '[products is_clearance="yes"]',
			'post_type'    => 'page',
		),
		true
	);

	if ( is_wp_error( $page_id ) ) {
		\wc_get_logger()->error( 'Failed to create clearance page. ' . $page_id->get_error_message() );
		return __( 'Failed to create clearance section page.', 'wc-clearance' );
	}

	return sprintf(
		/* translators: %s: URL to edit the created clearance page */
		__( 'Clearance section page created. View page: %s', 'wc-clearance' ),
		get_edit_post_link( $page_id, 'raw' )
	);
}
