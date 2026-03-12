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
 * Check if the clearance section page exists.
 *
 * @since 1.0.0
 * @throws \UnexpectedValueException If the stored option value is not an integer.
 */
function clearance_page_exists(): bool {
	$existing_id = get_option( CLEARANCE_PAGE_OPTION, null );

	if ( is_null( $existing_id ) ) {
		return false;
	}

	if ( ! is_int( $existing_id ) ) {
		throw new \UnexpectedValueException( 'Clearance page option must be an integer.' );
	}

	$existing_page = get_post( $existing_id );

	return $existing_page instanceof \WP_Post && 'page' === $existing_page->post_type;
}

/**
 * Create the clearance section page.
 *
 * @since 1.0.0
 * @throws \RuntimeException If the page could not be created.
 */
function create_clearance_page(): void {
	$result = wp_insert_post(
		array(
			'post_title'   => __( 'Clearance', 'wc-clearance' ),
			'post_name'    => 'clearance',
			'post_status'  => 'draft',
			'post_content' => '[products is_clearance="yes"]',
			'post_type'    => 'page',
		),
		true
	);

	if ( is_wp_error( $result ) ) {
		throw new \RuntimeException( $result->get_error_message() );
	}

	update_option( CLEARANCE_PAGE_OPTION, $result );
}
