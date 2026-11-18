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
 * Returns the title of the outlet page.
 *
 * Returns null when the outlet page is unavailable.
 *
 * @internal
 */
function get_outlet_page_title(): ?string {
	$page_id = get_outlet_page_id();
	if ( null === $page_id ) {
		return null;
	}

	$page = get_post( $page_id );
	if ( ! $page instanceof \WP_Post ) {
		return null;
	}
	if ( 'page' !== $page->post_type ) {
		return null;
	}

	return get_the_title( $page );
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
	register_outlet_page_template();
	add_filter( 'request', 'OutletPro\\filter_outlet_page_request_hook' );
	add_action( 'pre_get_posts', 'OutletPro\\outlet_page_pre_get_posts_hook', 20 );
	add_action( 'wp', 'OutletPro\\outlet_page_post_global_hook' );
	add_filter( 'single_term_title', 'OutletPro\\outlet_page_title_hook' );
	add_filter( 'term_link', 'OutletPro\\outlet_page_term_link_hook', 10, 2 );
	add_filter( 'woocommerce_get_breadcrumb', 'OutletPro\\outlet_page_breadcrumb_hook' );
	add_action( 'admin_bar_menu', 'OutletPro\\admin_bar_hook', 81 );
}

/**
 * Helper to de-initialize page integrations back to the uninitialized state.
 *
 * @internal
 */
function deinit_page(): void {
	remove_filter( 'request', 'OutletPro\\filter_outlet_page_request_hook' );
	remove_action( 'pre_get_posts', 'OutletPro\\outlet_page_pre_get_posts_hook', 20 );
	remove_action( 'wp', 'OutletPro\\outlet_page_post_global_hook' );
	remove_filter( 'single_term_title', 'OutletPro\\outlet_page_title_hook' );
	remove_filter( 'term_link', 'OutletPro\\outlet_page_term_link_hook', 10 );
	remove_filter( 'woocommerce_get_breadcrumb', 'OutletPro\\outlet_page_breadcrumb_hook' );
	remove_action( 'admin_bar_menu', 'OutletPro\\admin_bar_hook', 81 );

	$templates = \WP_Block_Templates_Registry::get_instance()->get_all_registered();

	foreach ( $templates as $template_name => $template ) {
		if ( 0 !== strpos( $template_name, 'outletpro//' ) ) {
			continue;
		}

		unregister_block_template( $template_name );
	}
}

/**
 * Sets the global post variable for the outlet page.
 *
 * Fired by `wp`, after WordPress has initialized the request globals.
 *
 * @internal WordPress action hook
 */
function outlet_page_post_global_hook(): void {
	if ( is_admin() ) {
		return;
	}
	if ( true !== get_query_var( 'outletpro' ) ) {
		return;
	}
	try {
		$page_id = get_outlet_page_id();
	} catch ( \UnexpectedValueException $e ) {
		wc_get_logger()->error( 'Outlet Page ID could not be retrieved.' );
		return;
	}
	if ( null === $page_id ) {
		return;
	}
	$page = get_post( $page_id );
	if ( ! $page instanceof \WP_Post ) {
		wc_get_logger()->error( 'Outlet Page could not be retrieved.' );
		return;
	}
	if ( 'page' !== $page->post_type ) {
		wc_get_logger()->error( 'Outlet Page could not be retrieved.' );
		return;
	}
	$GLOBALS['post'] = $page; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- 🤠
	setup_postdata( $page );
}

/**
 * Register the outlet page template.
 *
 * @internal
 */
function register_outlet_page_template(): void {
	$template_path = dirname( PLUGIN_FILE ) . '/templates/outlet-page.html';

	if ( ! is_readable( $template_path ) ) {
		return;
	}

	$template_content = file_get_contents( $template_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Loading local template file from plugin directory.

	if ( false === $template_content ) {
		return;
	}

	register_block_template(
		'outletpro//outlet-page',
		array(
			'title'       => __( 'Outlet page', 'outletpro' ),
			'description' => __( 'Wide page template for the outlet page.', 'outletpro' ),
			'post_types'  => array( 'page' ),
			'content'     => $template_content,
			'plugin'      => 'outletpro',
		)
	);
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

/**
 * Prepare requests for the outlet page.
 *
 * Responds with the existing Outlet term archive at the configured Page URL.
 *
 * Fired by `request`.
 *
 * @internal WordPress filter hook
 * @param array<string, mixed> $vars Parsed request variables.
 * @return array<string, mixed> Request variables.
 */
function filter_outlet_page_request_hook( array $vars ): array {
	if ( is_admin() ) {
		return $vars;
	}
	try {
		$page_id = get_outlet_page_id();
	} catch ( \UnexpectedValueException $e ) {
		wc_get_logger()->error( 'Outlet Page ID could not be retrieved.' );
		return $vars;
	}
	if ( null === $page_id ) {
		return $vars;
	}
	$page = get_post( $page_id );
	if ( ! $page instanceof \WP_Post ) {
		return $vars;
	}
	if ( 'page' !== $page->post_type ) {
		return $vars;
	}
	if ( 'trash' === $page->post_status ) {
		return $vars;
	}
	$matches_path = ( $vars['pagename'] ?? null ) === get_page_uri( $page );
	$matches_id   = filter_var( $vars['page_id'] ?? null, FILTER_VALIDATE_INT ) === $page->ID;
	if ( ! $matches_path && ! $matches_id ) {
		return $vars;
	}
	if ( 'outlet-page' === get_page_template_slug( $page ) ) {
		return $vars;
	}
	if ( 'publish' !== $page->post_status ) {
		if ( ! current_user_can( 'edit_post', $page->ID ) ) {
			return $vars;
		}
	}
	if ( post_password_required( $page ) ) {
		return $vars;
	}
	unset( $vars['pagename'], $vars['page_id'], $vars['name'], $vars['post_type'] );
	$vars['taxonomy']  = OUTLET_STATUS_TAXONOMY;
	$vars['term']      = OUTLET_STATUS_CANONICAL_TERM;
	$vars['outletpro'] = true;
	return $vars;
}

/**
 * Prepare the main query.
 *
 * Runs after WooCommerce's pre_get_posts to preserve its query arguments.
 *
 * Fired by `pre_get_posts`.
 *
 * @internal WordPress action hook
 * @param \WP_Query $query Main product query.
 */
function outlet_page_pre_get_posts_hook( \WP_Query $query ): void {
	if ( ! $query->is_main_query() ) {
		return;
	}
	if ( true !== $query->get( 'outletpro' ) ) {
		return;
	}
	// Set this after query parsing so Outlet remains a term archive, not Shop.
	$query->set( 'post_type', 'product' );
	$outlet_clauses = array( 'relation' => 'AND' );
	$facets         = array(
		'product_cat' => get_category_filter_query_param(),
	);
	foreach ( $facets as $taxonomy => $slug ) {
		if ( null === $slug ) {
			continue;
		}
		$outlet_clauses[] = array(
			'taxonomy' => $taxonomy,
			'field'    => 'slug',
			'terms'    => array( $slug ),
		);
	}
	foreach ( array( get_price_filter_query_param(), get_discount_filter_query_param() ) as $slug ) {
		if ( null === $slug ) {
			continue;
		}
		$outlet_clauses[] = array(
			'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
			'field'    => 'slug',
			'terms'    => array( $slug ),
		);
	}
	$existing = $query->get( 'tax_query' );
	if ( ! empty( $existing ) ) {
		$outlet_clauses[] = $existing;
	}
	$query->set( 'tax_query', $outlet_clauses ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Indexed product facets.
}

/**
 * Adds an edit page link on the admin toolbar for the outlet page.
 *
 * Fired by `admin_bar_menu`.
 *
 * @internal WordPress action hook
 * @param \WP_Admin_Bar $admin_bar Toolbar instance.
 */
function admin_bar_hook( \WP_Admin_Bar $admin_bar ): void {
	if ( is_admin() ) {
		return;
	}
	if ( true !== get_query_var( 'outletpro' ) ) {
		return;
	}
	try {
		$page_id = get_outlet_page_id();
	} catch ( \UnexpectedValueException $e ) {
		wc_get_logger()->error( 'Outlet Page ID could not be retrieved.' );
		return;
	}
	if ( null === $page_id ) {
		return;
	}
	$edit_link = get_edit_post_link( $page_id );
	if ( ! $edit_link ) {
		return;
	}
	$admin_bar->add_node(
		array(
			'id'    => 'edit',
			'title' => get_post_type_object( 'page' )->labels->edit_item,
			'href'  => $edit_link,
		)
	);
}

/**
 * Use the outlet page title in the canonical term archive.
 *
 * Fired by `single_term_title`.
 *
 * @internal WordPress filter hook
 * @param string $title Original term title.
 */
function outlet_page_title_hook( string $title ): string {
	if ( true !== get_query_var( 'outletpro' ) ) {
		return $title;
	}
	try {
		$page_title = get_outlet_page_title();
	} catch ( \UnexpectedValueException $e ) {
		wc_get_logger()->error( 'Outlet Page ID could not be retrieved.' );
		return $title;
	}
	if ( null === $page_title ) {
		return $title;
	}
	return $page_title;
}

/**
 * Replaces the canonical term URL with the outlet page URL.
 *
 * Fired by `term_link`.
 *
 * @internal WordPress filter hook
 * @param string   $url Original term URL.
 * @param \WP_Term $term Linked term.
 */
function outlet_page_term_link_hook( string $url, \WP_Term $term ): string {
	if ( OUTLET_STATUS_TAXONOMY !== $term->taxonomy ) {
		return $url;
	}
	if ( OUTLET_STATUS_CANONICAL_TERM !== $term->slug ) {
		return $url;
	}
	try {
		return get_outlet_permalink() ?? $url;
	} catch ( \UnexpectedValueException | \RuntimeException $e ) {
		wc_get_logger()->error( 'Outlet Page URL could not be retrieved.' );
		return $url;
	}
}

/**
 * Filters breadcrumbs to place the outlet page within the shop page.
 *
 * Fired by `woocommerce_get_breadcrumb`.
 *
 * @internal WooCommerce filter hook
 * @param array<array{string,string}> $crumbs Native breadcrumb trail.
 * @return array<array{string,string}> Outlet breadcrumb trail.
 */
function outlet_page_breadcrumb_hook( array $crumbs ): array {
	if ( ! is_tax( OUTLET_STATUS_TAXONOMY, OUTLET_STATUS_CANONICAL_TERM ) ) {
		return $crumbs;
	}
	$label         = get_taxonomy( OUTLET_STATUS_TAXONOMY )->labels->name;
	$shop_id       = wc_get_page_id( 'shop' );
	$front_page_id = 0;
	if ( 'page' === get_option( 'show_on_front' ) ) {
		$front_page_id = filter_var( get_option( 'page_on_front' ), FILTER_VALIDATE_INT );
	}
	foreach ( $crumbs as $index => $crumb ) {
		if ( $label !== $crumb[0] ) {
			continue;
		}
		if ( '' !== $crumb[1] ) {
			continue;
		}
		if ( 0 < $shop_id && $shop_id !== $front_page_id ) {
			$crumbs[ $index ] = array( get_the_title( $shop_id ), get_permalink( $shop_id ) );
		} else {
			unset( $crumbs[ $index ] );
		}
		break;
	}
	return array_values( $crumbs );
}
