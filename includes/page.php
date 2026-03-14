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
 * Helper to initialize page features.
 *
 * @since 1.0.0
 */
function init_page(): void {
	add_filter( 'display_post_states', 'WC_Clearance\display_clearance_page_state_hook', 10, 2 );
}

/**
 * Add a "Clearance Section Page" label to the clearance page in the admin listing table.
 *
 * Fired by `display_post_states`.
 *
 * @param string[] $post_states An array of post display states.
 * @param \WP_Post $post        The current post object.
 * @return string[] Modified post display states.
 * @internal WordPress filter
 */
function display_clearance_page_state_hook( array $post_states, \WP_Post $post ): array {
	$page_id = get_option( CLEARANCE_PAGE_OPTION );

	if ( $page_id > 0 ) {
		$clearance_page = get_post( $page_id );
		if ( $clearance_page instanceof \WP_Post && $clearance_page->ID === $post->ID ) {
			$post_states['wc_clearance_page'] = __( 'Clearance Section Page', 'wc-clearance' );
		}
	}

	return $post_states;
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
 * @throws \RuntimeException If it cannot be determined whether the clearance page already exists.
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
			'Could not determine whether the clearance page already exists.',
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
