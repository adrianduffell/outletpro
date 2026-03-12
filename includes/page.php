<?php
/**
 * Page functions.
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
 * Create the clearance section page.
 *
 * @since 1.0.0
 * @throws \RuntimeException If the page could not be created.
 */
function create_clearance_page(): void {
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
}
