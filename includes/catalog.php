<?php
/**
 * Outlet catalog requests.
 *
 * @package OutletPro
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Initialize the outlet's product archive integration.
 *
 * @internal
 */
function init_catalog(): void {
	add_filter( 'request', 'OutletPro\\outlet_catalog_request_hook' );
	add_action( 'pre_get_posts', 'OutletPro\\outlet_catalog_query_hook', 9 );
	add_action( 'pre_get_posts', 'OutletPro\\outlet_catalog_identity_hook', 20 );
	add_filter( 'woocommerce_page_title', 'OutletPro\\outlet_catalog_title_hook' );
	add_filter( 'get_the_archive_title', 'OutletPro\\outlet_catalog_title_hook' );
	add_filter( 'post_type_archive_title', 'OutletPro\\outlet_catalog_title_hook' );
	add_filter( 'woocommerce_get_breadcrumb', 'OutletPro\\outlet_catalog_breadcrumb_hook' );
	add_action( 'woocommerce_breadcrumb', 'OutletPro\\outlet_catalog_breadcrumb_fallback_hook', 5, 2 );
	add_action( 'template_redirect', 'OutletPro\\outlet_catalog_classic_header_hook' );
	add_filter( 'redirect_canonical', 'OutletPro\\outlet_catalog_canonical_hook' );
	add_action( 'woocommerce_archive_description', 'OutletPro\\outlet_catalog_description_hook', 5 );
	add_filter( 'woocommerce_widget_get_current_page_url', 'OutletPro\\outlet_catalog_widget_url_hook' );
	add_action( 'wp_after_insert_post', 'OutletPro\\refresh_outlet_catalog_routes_hook', 10, 3 );
	add_action( 'updated_option', 'OutletPro\\refresh_outlet_catalog_option_hook', 10, 1 );
	init_catalog_templates();

	try {
		$page = get_catalog_page();
		if ( null === $page ) {
			return;
		}
		$path = get_page_uri( $page );
		add_rewrite_rule( '^' . preg_quote( $path, '/' ) . '/page/([0-9]+)/?$', 'index.php?pagename=' . $path . '&paged=$matches[1]', 'top' );
	} catch ( \RuntimeException $e ) {
		wc_get_logger()->error( 'Outlet catalog routes could not be registered.' );
	}
}

/**
 * Remove catalog integrations when page integrations are deinitialized.
 *
 * @internal
 */
function deinit_catalog(): void {
	remove_filter( 'request', 'OutletPro\\outlet_catalog_request_hook' );
	remove_action( 'pre_get_posts', 'OutletPro\\outlet_catalog_query_hook', 9 );
	remove_action( 'pre_get_posts', 'OutletPro\\outlet_catalog_identity_hook', 20 );
	remove_filter( 'woocommerce_page_title', 'OutletPro\\outlet_catalog_title_hook' );
	remove_filter( 'get_the_archive_title', 'OutletPro\\outlet_catalog_title_hook' );
	remove_filter( 'post_type_archive_title', 'OutletPro\\outlet_catalog_title_hook' );
	remove_filter( 'woocommerce_get_breadcrumb', 'OutletPro\\outlet_catalog_breadcrumb_hook' );
	remove_action( 'woocommerce_breadcrumb', 'OutletPro\\outlet_catalog_breadcrumb_fallback_hook', 5 );
	remove_action( 'template_redirect', 'OutletPro\\outlet_catalog_classic_header_hook' );
	if ( 5 === has_action( 'woocommerce_before_main_content', 'woocommerce_product_taxonomy_archive_header' ) ) {
		remove_action( 'woocommerce_before_main_content', 'woocommerce_product_taxonomy_archive_header', 5 );
		add_action( 'woocommerce_shop_loop_header', 'woocommerce_product_taxonomy_archive_header' );
		add_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description' );
	}
	remove_filter( 'redirect_canonical', 'OutletPro\\outlet_catalog_canonical_hook' );
	remove_action( 'woocommerce_archive_description', 'OutletPro\\outlet_catalog_description_hook', 5 );
	remove_filter( 'woocommerce_widget_get_current_page_url', 'OutletPro\\outlet_catalog_widget_url_hook' );
	remove_action( 'wp_after_insert_post', 'OutletPro\\refresh_outlet_catalog_routes_hook' );
	remove_action( 'updated_option', 'OutletPro\\refresh_outlet_catalog_option_hook' );
	remove_filter( 'archive_template_hierarchy', 'OutletPro\\outlet_catalog_hierarchy_hook' );
	remove_filter( 'page_template_hierarchy', 'OutletPro\\outlet_catalog_password_template_hook' );
	remove_filter( 'block_editor_settings_all', 'OutletPro\\lock_outlet_page_editor_hook' );
}

/**
 * Retrieve the configured outlet Page, including drafts for editor previews.
 *
 * @internal
 * @return \WP_Post|null Outlet Page, or null when none is configured.
 */
function get_catalog_page(): ?\WP_Post {
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
	if ( 'trash' === $page->post_status ) {
		return null;
	}
	return $page;
}

/**
 * Route the outlet Page to a product archive without changing the Shop setting.
 *
 * Fired by `request`.
 *
 * @internal WordPress filter hook
 * @param array<string, mixed> $vars Parsed request variables.
 * @return array<string, mixed> Request variables.
 */
function outlet_catalog_request_hook( array $vars ): array {
	if ( is_admin() ) {
		return $vars;
	}
	try {
		$page = get_catalog_page();
	} catch ( \UnexpectedValueException $e ) {
		wc_get_logger()->error( 'Outlet Page ID could not be retrieved.' );
		return $vars;
	}
	if ( null === $page ) {
		return $vars;
	}
	$matches_path = ( $vars['pagename'] ?? null ) === get_page_uri( $page );
	$matches_id   = filter_var( $vars['page_id'] ?? null, FILTER_VALIDATE_INT ) === $page->ID;
	if ( ! $matches_path && ! $matches_id ) {
		return $vars;
	}
	if ( 'publish' !== $page->post_status ) {
		if ( empty( $vars['preview'] ) ) {
			return $vars;
		}
		if ( ! current_user_can( 'edit_post', $page->ID ) ) {
			return $vars;
		}
	}
	if ( post_password_required( $page ) ) {
		return $vars;
	}
	unset( $vars['pagename'], $vars['page_id'], $vars['name'] );
	$vars['post_type']         = 'product';
	$vars['outletpro_catalog'] = $page->ID;
	return $vars;
}

/**
 * Add outlet and navigation restrictions before WooCommerce builds its query.
 *
 * Fired by `pre_get_posts`.
 *
 * @internal WordPress action hook
 * @param \WP_Query $query Product query.
 */
function outlet_catalog_query_hook( \WP_Query $query ): void {
	if ( ! $query->is_main_query() ) {
		return;
	}
	if ( ! $query->get( 'outletpro_catalog' ) ) {
		return;
	}
	// A second catalog needs archive semantics even when the theme has not
	// registered WooCommerce's primary product archive yet (for example in tests).
	$query->is_page              = false;
	$query->is_singular          = false;
	$query->is_home              = false;
	$query->is_archive           = true;
	$query->is_post_type_archive = true;
	$restrictions                = array(
		'relation' => 'AND',
		array(
			'taxonomy' => OUTLET_STATUS_TAXONOMY,
			'field'    => 'slug',
			'terms'    => array( OUTLET_STATUS_CANONICAL_TERM ),
		),
	);
	$facets                      = array(
		'product_cat' => get_category_filter_query_param(),
	);
	foreach ( $facets as $taxonomy => $slug ) {
		if ( null === $slug ) {
			continue;
		}

		$restrictions[] = array(
			'taxonomy' => $taxonomy,
			'field'    => 'slug',
			'terms'    => array( $slug ),
		);
	}
	foreach ( array( get_price_filter_query_param(), get_discount_filter_query_param() ) as $slug ) {
		if ( null === $slug ) {
			continue;
		}

		$restrictions[] = array(
			'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
			'field'    => 'slug',
			'terms'    => array( $slug ),
		);
	}
	$existing = $query->get( 'tax_query' );
	if ( ! empty( $existing ) ) {
		$restrictions[] = $existing;
	}
	$query->set( 'tax_query', $restrictions ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Indexed product facets.
}

/**
 * Preserve the outlet Page identity after WooCommerce assigns the Shop Page.
 *
 * Fired by `pre_get_posts`.
 *
 * @internal WordPress action hook
 * @param \WP_Query $query Main product query.
 */
function outlet_catalog_identity_hook( \WP_Query $query ): void {
	if ( ! $query->is_main_query() ) {
		return;
	}
	$page_id = $query->get( 'outletpro_catalog' );
	if ( ! is_int( $page_id ) ) {
		return;
	}
	$query->queried_object    = get_post( $page_id );
	$query->queried_object_id = $page_id;
}

/**
 * Use the outlet title on the shared catalog.
 *
 * Fired by `woocommerce_page_title` and `get_the_archive_title`.
 *
 * @internal WordPress filter hook
 * @param string $title Original archive title.
 */
function outlet_catalog_title_hook( string $title ): string {
	$page_id = get_query_var( 'outletpro_catalog' );
	return is_int( $page_id ) ? get_the_title( $page_id ) : $title;
}

/**
 * Use the Outlet Page hierarchy in WooCommerce's archive breadcrumb trail.
 *
 * Fired by `woocommerce_get_breadcrumb`.
 *
 * @internal WooCommerce filter hook
 * @param array<array{string,string}> $crumbs Native breadcrumb trail.
 * @return array<array{string,string}> Outlet breadcrumb trail.
 */
function outlet_catalog_breadcrumb_hook( array $crumbs ): array {
	$page_id = get_query_var( 'outletpro_catalog' );
	if ( ! is_int( $page_id ) ) {
		return $crumbs;
	}
	if ( is_404() ) {
		return $crumbs;
	}
	$home_url      = apply_filters( 'woocommerce_breadcrumb_home_url', home_url() );
	$front_page_id = filter_var( get_option( 'page_on_front' ), FILTER_VALIDATE_INT );
	$offset        = isset( $crumbs[0][1] ) && $home_url === $crumbs[0][1] ? 1 : 0;
	$pages         = array();
	foreach ( array_reverse( get_post_ancestors( $page_id ) ) as $ancestor_id ) {
		if ( $front_page_id === $ancestor_id ) {
			continue;
		}
		$pages[] = array( wp_strip_all_tags( get_the_title( $ancestor_id ) ), get_permalink( $ancestor_id ) );
	}
	$pages[] = array( wp_strip_all_tags( get_the_title( $page_id ) ), get_permalink( $page_id ) );
	// WooCommerce omits the Shop crumb when that Page is the homepage.
	$shop_crumb_count = wc_get_page_id( 'shop' ) === $front_page_id ? 0 : 1;
	return array_merge( array_slice( $crumbs, 0, $offset ), $pages, array_slice( $crumbs, $offset + $shop_crumb_count ) );
}

/**
 * Render the Outlet trail when WooCommerce skips its archive-as-homepage trail.
 *
 * Fired by `woocommerce_breadcrumb`, before breadcrumb structured data is built.
 *
 * @internal WooCommerce action hook
 * @param \WC_Breadcrumb      $breadcrumbs Native breadcrumb generator.
 * @param array<string,mixed> $args Native breadcrumb template arguments.
 */
function outlet_catalog_breadcrumb_fallback_hook( \WC_Breadcrumb $breadcrumbs, array $args ): void {
	if ( ! is_int( get_query_var( 'outletpro_catalog' ) ) ) {
		return;
	}
	if ( ! empty( $args['breadcrumb'] ) ) {
		return;
	}
	$args['breadcrumb'] = $breadcrumbs->get_breadcrumb();
	wc_get_template( 'global/breadcrumb.php', $args );
}

/**
 * Prevent WordPress from canonicalizing the outlet to the Shop archive.
 *
 * Fired by `redirect_canonical`.
 *
 * @internal WordPress filter hook
 * @param string|false $redirect Canonical redirect.
 * @return string|false Canonical redirect.
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint
 */
function outlet_catalog_canonical_hook( $redirect ) {
	return get_query_var( 'outletpro_catalog' ) ? false : $redirect;
}

/**
 * Place the classic archive header above the theme's product/sidebar wrappers.
 *
 * Fired by `template_redirect`.
 *
 * @internal WordPress action hook
 */
function outlet_catalog_classic_header_hook(): void {
	if ( wp_is_block_theme() ) {
		return;
	}
	if ( ! get_query_var( 'outletpro_catalog' ) ) {
		return;
	}
	if ( 10 !== has_action( 'woocommerce_shop_loop_header', 'woocommerce_product_taxonomy_archive_header' ) ) {
		return;
	}
	remove_action( 'woocommerce_shop_loop_header', 'woocommerce_product_taxonomy_archive_header' );
	remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description' );
	add_action( 'woocommerce_before_main_content', 'woocommerce_product_taxonomy_archive_header', 5 );
}

/**
 * Output outlet navigation in classic archive headers on every results page.
 *
 * Fired by `woocommerce_archive_description`.
 *
 * @internal WordPress action hook
 */
function outlet_catalog_description_hook(): void {
	$page_id = get_query_var( 'outletpro_catalog' );
	if ( ! is_int( $page_id ) ) {
		return;
	}
	try {
		echo render_outlet_page_content( $page_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress renders and filters Page content.
	} catch ( \RuntimeException $e ) {
		wc_get_logger()->error( 'Outlet navigation could not be rendered.' );
	}
}

/**
 * Keep classic filter submissions on the outlet URL.
 *
 * Fired by `woocommerce_widget_get_current_page_url`.
 *
 * @internal WordPress filter hook
 * @param string $url Widget target URL.
 */
function outlet_catalog_widget_url_hook( string $url ): string {
	$page_id = get_query_var( 'outletpro_catalog' );
	if ( ! is_int( $page_id ) ) {
		return $url;
	}
	$filters = array();
	wp_parse_str( wp_parse_url( $url, PHP_URL_QUERY ) ?? '', $filters );
	unset( $filters['page_id'], $filters['pagename'], $filters['post_type'], $filters['paged'] );
	foreach ( array( 'outlet_category', 'outlet_price', 'outlet_discount' ) as $name ) {
		$value = get_query_var( $name );
		if ( ! is_string( $value ) ) {
			continue;
		}
		if ( '' === $value ) {
			continue;
		}

		$filters[ $name ] = sanitize_text_field( $value );
	}
	return add_query_arg( $filters, get_permalink( $page_id ) );
}

/**
 * Refresh pagination routes after changing the outlet Page.
 *
 * Fired by `wp_after_insert_post`.
 *
 * @internal WordPress action hook
 * @param int      $post_id Saved post ID.
 * @param \WP_Post $post Saved post.
 * @param bool     $update Whether this was an update.
 */
function refresh_outlet_catalog_routes_hook( int $post_id, \WP_Post $post, bool $update ): void {
	if ( 'page' !== $post->post_type ) {
		return;
	}
	if ( ! $update ) {
		return;
	}
	if ( get_option( OUTLET_PAGE_OPTION ) != $post_id ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual -- WordPress may return option IDs as digit strings.
		return;
	}
	delete_option( 'rewrite_rules' );
}

/**
 * Refresh routes when the configured outlet changes.
 *
 * Fired by `updated_option`.
 *
 * @internal WordPress action hook
 * @param string $option Changed option name.
 */
function refresh_outlet_catalog_option_hook( string $option ): void {
	if ( OUTLET_PAGE_OPTION !== $option ) {
		return;
	}
	delete_option( 'rewrite_rules' );
}
