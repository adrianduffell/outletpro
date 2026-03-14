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
 * This performs heuristics on the {@see CLEARANCE_PAGE_OPTION} option value.
 *
 * It is considered to exist when the option exists and contains the page ID
 * of a WordPress page.
 *
 * If the option is missing, the clearance page is considered not registered
 * and the function returns false.
 *
 * Zero and non-digit values indicate a corrupted state and the page existence cannot
 * be determined. Exceptions are thrown in these cases.
 *
 * Trashed pages are ignored.
 *
 * @since 1.0.0
 * @throws \UnexpectedValueException If the stored option value is not an integer greater than zero.
 */
function clearance_page_exists(): bool {
	$page_id = get_option( CLEARANCE_PAGE_OPTION, false );

	// The option does not exist, therefore the page does not exist.
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

	// Validate post type and status.
	return $page instanceof \WP_Post
	&& 'page' === $page->post_type
	&& 'trash' !== $page->post_status;
}

/**
 * Create the clearance section page.
 *
 * Does nothing if a clearance page is already registered via the
 * {@see CLEARANCE_PAGE_OPTION} option, preventing duplicates. If
 * the option value is corrupted, an exception is thrown as page
 * creation cannot be safely performed.
 *
 * @since 1.0.0
 * @throws \RuntimeException If it cannot be determined whether the clearance page exists.
 * @throws \RuntimeException If the page could not be created.
 */
function create_clearance_page(): void {
	// Prevent duplicate pages from being created.
	try {
		if ( clearance_page_exists() ) {
			return;
		}
	} catch ( \UnexpectedValueException $e ) {
		throw new \RuntimeException(
			'Could not determine whether the clearance page exists.',
			0,
			$e
		);
	}

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

/**
 * Check whether the clearance section page exists and is published.
 *
 * @since 1.0.0
 * @throws \RuntimeException If it cannot be determined whether the clearance page already exists.
 */
function clearance_page_is_published(): bool {
	try {
		if ( ! clearance_page_exists() ) {
			return false;
		}
	} catch ( \UnexpectedValueException $e ) {
		throw new \RuntimeException(
			'Could not determine whether the clearance page already exists.',
			0,
			$e
		);
	}

	$page_id = get_option( CLEARANCE_PAGE_OPTION );
	$page    = get_post( $page_id );

	return $page instanceof \WP_Post
		&& 'publish' === $page->post_status;
}

/**
 * Get the clearance section page ID from the option.
 *
 * Accounts for the option value being stored as a string or int.
 * Validates the page ID is a positive integer. Zero and non-digit values
 * indicate a corrupted state and exceptions are thrown in these cases.
 *
 * @since 1.0.0
 * @throws \UnexpectedValueException If the stored option value is not an integer greater than zero.
 */
function get_clearance_page_id(): int {
	$value = get_option( CLEARANCE_PAGE_OPTION, null );

	if ( is_int( $value ) && $value > 0 ) {
		return $value;
	}

	// Option values are stored as strings in database and some caching layers.
	// Cast valid numeric strings to int.
	if ( is_string( $value ) && ctype_digit( $value ) && 0 !== $value ) {
		return (int) $value;
	}

	throw new \UnexpectedValueException( 'Invalid clearance page option value.' );
}
