<?php
/**
 * Tools functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * WordPress option key used to store the clearance section page ID.
 *
 * @since 1.0.0
 */
const CLEARANCE_PAGE_OPTION = 'wc_clearance_page_id';

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
	$existing_id   = (int) get_option( CLEARANCE_PAGE_OPTION );
	$existing_page = $existing_id > 0 ? get_post( $existing_id ) : null;

	if ( $existing_page instanceof \WP_Post && 'page' === $existing_page->post_type ) {
		return sprintf(
			/* translators: %s: URL to edit the existing clearance page */
			__( 'Clearance section page already exists. View page: %s', 'wc-clearance' ),
			get_edit_post_link( $existing_id, 'raw' )
		);
	}

	try {
		$page_id = create_clearance_page();
	} catch ( \RuntimeException $e ) {
		return __( 'Clearance section page could not be created.', 'wc-clearance' );
	}

	return sprintf(
		/* translators: %s: URL to edit the created clearance page */
		__( 'Clearance section page created. View page: %s', 'wc-clearance' ),
		get_edit_post_link( $page_id, 'raw' )
	);
}

/**
 * Create the clearance section page.
 *
 * @since 1.0.0
 * @throws \RuntimeException If the page could not be created.
 * @return int The created page ID.
 */
function create_clearance_page(): int {
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
		throw new \RuntimeException( $page_id->get_error_message() );
	}

	update_option( CLEARANCE_PAGE_OPTION, $page_id );

	return $page_id;
}
