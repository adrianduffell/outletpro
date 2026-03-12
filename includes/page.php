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
	$page_id = get_option( CLEARANCE_PAGE_OPTION, false );

	if ( false === $page_id ) {
		return false;
	}

	// Non-digit values are invalid and indicate a misconfiguration.
	if ( ! ctype_digit( (string) $page_id ) ) {
		throw new \UnexpectedValueException( 'Clearance page option is not a positive integer.' );
	}

	// At this point the value can only be an integer >= 0.
	// Cast to int because caching layers may have returned it as a string.
	$page_id = (int) $page_id;

	// Zero indicates a corrupted state.
	if ( 0 === $page_id ) {
		throw new \UnexpectedValueException( 'Clearance page option value is zero.' );
	}

	$page = get_post( $page_id );

	return $page instanceof \WP_Post && 'page' === $page->post_type;
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
