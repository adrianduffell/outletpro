<?php
/**
 * Page functions.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Returns the permalink for the outlet page.
 *
 * Returns null when the outlet page is not configured.
 *
 * @internal
 * @throws \RuntimeException If the configured outlet page permalink cannot be retrieved.
 */
function get_outlet_permalink(): ?string {
	$page_id = get_outlet_page_id();
	if ( null === $page_id ) {
		return null;
	}

	$permalink = get_permalink( $page_id );
	if ( ! is_string( $permalink ) ) {
		throw new \RuntimeException( 'Outlet page permalink could not be retrieved.' );
	}

	return $permalink;
}

/**
 * Returns the outlet page permalink with a filter query param.
 *
 * @internal
 * @param \WP_Term $term Product category or search index facet term.
 */
function get_outlet_permalink_with_filter( \WP_Term $term ): ?string {
	$permalink = get_outlet_permalink();
	if ( null === $permalink ) {
		return null;
	}

	if ( 'product_cat' === $term->taxonomy ) {
		return add_query_arg( 'outlet_category', $term->slug, $permalink );
	}
	if ( OUTLET_SEARCH_INDEX_TAXONOMY !== $term->taxonomy ) {
		return null;
	}

	if ( 1 !== preg_match( '/^outlet-(price|discount)-([0-9]+)$/D', $term->slug, $matches ) ) {
		return null;
	}
	$type   = $matches[1];
	$amount = filter_var( $matches[2], FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1 ) ) );
	if ( false === $amount ) {
		return null;
	}
	if ( 'price' === $type ) {
		return add_query_arg( 'outlet_price', $amount, $permalink );
	}
	if ( 'discount' === $type ) {
		if ( $amount > 100 ) {
			return null;
		}
		return add_query_arg( 'outlet_discount', $amount, $permalink );
	}

	return null;
}

/**
 * Helper to initialize page integrations.
 *
 * @internal
 */
function init_page(): void {
	init_catalog();
}

/**
 * Helper to de-initialize page integrations back to the uninitialized state.
 *
 * @internal
 */
function deinit_page(): void {
	deinit_catalog();
	$templates = \WP_Block_Templates_Registry::get_instance()->get_all_registered();

	foreach ( $templates as $template_name => $template ) {
		if ( 0 !== strpos( $template_name, 'outletpro//' ) ) {
			continue;
		}

		unregister_block_template( $template_name );
	}
}

/**
 * Check if the outlet page exists.
 *
 * This performs heuristics on the {@see OUTLET_PAGE_OPTION} option value.
 *
 * It is considered to exist when the option exists and contains the page ID
 * of a WordPress page.
 *
 * If the option is missing, the outlet page is considered not registered
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
function outlet_page_exists(): bool {
	$page_id = get_option( OUTLET_PAGE_OPTION, false );

	// The option does not exist, therefore the page does not exist.
	if ( false === $page_id ) {
		return false;
	}

	// Non-digit values are invalid and indicate a misconfiguration.
	if ( ! ctype_digit( (string) $page_id ) ) {
		throw new \UnexpectedValueException( 'Outlet page option is not a positive integer.' );
	}

	// At this point the value can only be an integer >= 0.
	// Cast to int because caching layers may have returned it as a string.
	$page_id = (int) $page_id;

	// Zero indicates a corrupted state.
	if ( 0 === $page_id ) {
		throw new \UnexpectedValueException( 'Outlet page option value is zero.' );
	}

	$page = get_post( $page_id );

	// Validate post type and status.
	return $page instanceof \WP_Post
	&& 'page' === $page->post_type
	&& 'trash' !== $page->post_status;
}

/**
 * Helper to report diagnostic info on the outlet page.
 *
 * @internal
 * @return array<string, array{0: string, 1: string}>
 */
function report_page(): array {
	$label = __( 'Page ID', 'outletpro' );
	try {
		$page_id = get_outlet_page_id();
	} catch ( \UnexpectedValueException $e ) {
		return array( 'outlet-page-id' => array( $label, __( 'Not found', 'outletpro' ) ) );
	}
	$page_id = get_outlet_page_id();
	$page    = $page_id ? get_post( $page_id ) : null;

	if ( ! $page instanceof \WP_Post || 'page' !== $page->post_type ) {
		return array( 'outlet-page-id' => array( $label, __( 'Not found', 'outletpro' ) ) );
	}

	$status_object = get_post_status_object( $page->post_status );
	$status_label  = $status_object ? $status_object->label : $page->post_status;

	return array( 'outlet-page-id' => array( $label, sprintf( '%d (%s)', $page_id, $status_label ) ) );
}

/**
 * Create the outlet page.
 *
 * Does nothing if a outlet page is already registered via the
 * {@see OUTLET_PAGE_OPTION} option, preventing duplicates. If
 * the option value is corrupted, an exception is thrown as page
 * creation cannot be safely performed.
 *
 * @since 1.0.0
 * @throws \RuntimeException If it cannot be determined whether the outlet page exists.
 * @throws \RuntimeException If the page could not be created.
 */
function create_outlet_page(): void {
	// Prevent duplicate pages from being created.
	try {
		if ( outlet_page_exists() ) {
			return;
		}
	} catch ( \UnexpectedValueException $e ) {
		throw new \RuntimeException(
			'Could not determine whether the outlet page exists.',
			0,
			$e // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Passing previous exception for chaining.
		);
	}

	$post_content = '';

	$page_id = wp_insert_post(
		array(
			'post_title'  => __( 'Outlet', 'outletpro' ),
			'post_name'   => 'outlet',
			'post_status' => 'draft',
			'post_type'   => 'page',
		),
		true
	);

	if ( is_wp_error( $page_id ) ) {
		throw new \RuntimeException( 'Could not create outlet page.' );
	}

	update_option( OUTLET_PAGE_OPTION, $page_id );

	$categories = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => 0,
			'hide_empty' => false,
			'fields'     => 'ids',
			'number'     => 7,
		)
	);
	if ( is_wp_error( $categories ) ) {
		wc_get_logger()->error( 'Product categories could not be retrieved.' );
		throw new \RuntimeException( 'Product categories could not be retrieved.' );
	}

	$category_count = count( $categories );
	switch ( $category_count ) {
		case 0:
		case 1:
		case 2:
			$navigation_pattern = 'outletpro/outlet-navigation-price-tiers';
			break;
		case 4:
			$navigation_pattern = 'outletpro/outlet-navigation-4';
			break;
		case 6:
			$navigation_pattern = 'outletpro/outlet-navigation-6';
			break;
		default:
			$navigation_pattern = 'outletpro/outlet-navigation-3';
	}
	$navigation_content = get_pattern_content( $navigation_pattern );
	if ( '' !== $navigation_content ) {
		$post_content = $navigation_content . "\n\n" . $post_content;
	}

	$result = wp_update_post(
		array(
			'ID'           => $page_id,
			'post_content' => $post_content,
		),
		true
	);

	if ( is_wp_error( $result ) ) {
		throw new \RuntimeException( 'Could not update outlet page content.' );
	}
	$template = create_outlet_catalog_template();
	if ( null === $template ) {
		return;
	}

	update_post_meta( $page_id, '_wp_page_template', $template );
}

/**
 * Check whether the outlet page exists and is published.
 *
 * @since 1.0.0
 * @throws \RuntimeException If it cannot be determined whether the outlet page already exists.
 */
function outlet_page_is_published(): bool {
	try {
		if ( ! outlet_page_exists() ) {
			return false;
		}
	} catch ( \UnexpectedValueException $e ) {
		throw new \RuntimeException(
			'Could not determine whether the outlet page already exists.',
			0,
			$e // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Passing previous exception for chaining.
		);
	}

	$page_id = get_option( OUTLET_PAGE_OPTION );
	$page    = get_post( $page_id );

	return $page instanceof \WP_Post
		&& 'publish' === $page->post_status;
}
