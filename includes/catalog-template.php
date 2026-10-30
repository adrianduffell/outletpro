<?php
/**
 * Shared catalog templates with editable outlet navigation.
 *
 * @package OutletPro
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Register catalog template and Page editor integrations.
 *
 * @internal
 */
function init_catalog_templates(): void {
	add_filter( 'archive_template_hierarchy', 'OutletPro\\outlet_catalog_hierarchy_hook' );
	add_filter( 'page_template_hierarchy', 'OutletPro\\outlet_catalog_password_template_hook' );
	add_filter( 'block_editor_settings_all', 'OutletPro\\lock_outlet_page_editor_hook', 10, 2 );
}

/**
 * Keep block-theme Outlet Page content in its editable template.
 *
 * Fired by `block_editor_settings_all`.
 *
 * @internal WordPress filter hook
 * @param array<string, mixed>     $settings Existing editor settings.
 * @param \WP_Block_Editor_Context $context Current editor context.
 * @return array<string, mixed> Modified editor settings.
 */
function lock_outlet_page_editor_hook( array $settings, \WP_Block_Editor_Context $context ): array {
	if ( ! wp_is_block_theme() ) {
		return $settings;
	}
	if ( 'core/edit-post' !== $context->name ) {
		return $settings;
	}
	if ( ! $context->post instanceof \WP_Post ) {
		return $settings;
	}
	if ( 'page' !== $context->post->post_type ) {
		return $settings;
	}
	$page_id = filter_var( get_option( OUTLET_PAGE_OPTION ), FILTER_VALIDATE_INT );
	if ( $context->post->ID !== $page_id ) {
		return $settings;
	}
	$settings['templateLock'] = 'all';
	return $settings;
}

/**
 * Resolve the active catalog, including saved Site Editor customizations.
 *
 * @internal
 * @throws \RuntimeException When WooCommerce cannot provide a catalog template.
 */
function get_shop_catalog_template(): \WP_Block_Template {
	$templates = get_block_templates( array( 'slug__in' => array( 'archive-product' ) ) );
	foreach ( $templates as $template ) {
		if ( 'archive-product' === $template->slug ) {
			return $template;
		}
	}
	throw new \RuntimeException( 'Shop catalog template could not be retrieved.' );
}

/**
 * Save a theme-specific copy of the resolved shop catalog in WordPress.
 *
 * Existing Outlet template customizations are kept when another Page is created.
 *
 * @internal
 * @param string $navigation Initial editable navigation blocks.
 * @return string|null Template slug, or null for classic themes.
 * @throws \RuntimeException When the template cannot be saved.
 */
function create_outlet_catalog_template( string $navigation ): ?string {
	if ( ! wp_is_block_theme() ) {
		return null;
	}
	$existing = get_block_template( get_stylesheet() . '//outlet-catalog' );
	if ( $existing instanceof \WP_Block_Template ) {
		return $existing->slug;
	}
	$shop        = get_shop_catalog_template();
	$template_id = wp_insert_post(
		array(
			'post_type'    => 'wp_template',
			'post_status'  => 'publish',
			'post_name'    => 'outlet-catalog',
			'post_title'   => __( 'Outlet catalog', 'outletpro' ),
			'post_content' => wp_slash( compose_outlet_catalog_content( $shop->content, $navigation ) ),
		),
		true
	);
	if ( is_wp_error( $template_id ) ) {
		wc_get_logger()->error( 'Outlet catalog template could not be saved.' );
		throw new \RuntimeException( 'Outlet catalog template could not be saved.' );
	}
	$result = wp_set_object_terms( $template_id, get_stylesheet(), 'wp_theme' );
	if ( is_wp_error( $result ) ) {
		wc_get_logger()->error( 'Outlet catalog theme could not be saved.' );
		throw new \RuntimeException( 'Outlet catalog theme could not be saved.' );
	}
	return 'outlet-catalog';
}

/**
 * Insert navigation after the archive heading, with a main-region fallback.
 *
 * @internal
 * @param string $content Active shop template markup.
 * @param string $navigation Initial editable navigation blocks.
 */
function compose_outlet_catalog_content( string $content, string $navigation ): string {
	$blocks  = resolve_pattern_blocks( parse_blocks( $content ) );
	$content = serialize_blocks( $blocks );
	$heading = find_catalog_heading( $blocks );
	$region  = '<!-- wp:group {"align":"wide","className":"outletpro-navigation","layout":{"type":"default"}} --><div class="wp-block-group alignwide outletpro-navigation"><!-- wp:paragraph --><p>While supplies last</p><!-- /wp:paragraph -->' . $navigation . '</div><!-- /wp:group -->';
	if ( null !== $heading ) {
		$offset = strpos( $content, $heading );
		return substr_replace( $content, $region, $offset + strlen( $heading ), 0 );
	}
	if ( 1 === preg_match( '/<main\b[^>]*>/', $content, $matches, PREG_OFFSET_CAPTURE ) ) {
		return substr_replace( $content, $region, $matches[0][1] + strlen( $matches[0][0] ), 0 );
	}
	// Templates without a main landmark still keep navigation before the catalog row.
	foreach ( $blocks as $index => $block ) {
		if ( 'core/template-part' === $block['blockName'] ) {
			continue;
		}
		array_splice( $blocks, $index, 0, parse_blocks( $region ) );
		return serialize_blocks( $blocks );
	}
	return $region . $content;
}

/**
 * Find the archive heading without selecting individual product titles.
 *
 * @internal
 * @param array<array<string, mixed>> $blocks Parsed template blocks.
 */
function find_catalog_heading( array $blocks ): ?string {
	foreach ( $blocks as $block ) {
		if ( 'core/query-title' === $block['blockName'] && 'archive' === ( $block['attrs']['type'] ?? 'archive' ) ) {
			return serialize_block( $block );
		}
		$heading = find_catalog_heading( $block['innerBlocks'] );
		if ( null !== $heading ) {
			return $heading;
		}
	}
	return null;
}

/**
 * Prefer the Outlet Page's chosen template over the product archive layout.
 *
 * Fired by `archive_template_hierarchy`.
 *
 * @internal WordPress filter hook
 * @param string[] $templates Template candidates.
 * @return string[] Template candidates.
 */
function outlet_catalog_hierarchy_hook( array $templates ): array {
	if ( ! wp_is_block_theme() ) {
		return $templates;
	}
	$page_id = get_query_var( 'outletpro_catalog' );
	if ( ! is_int( $page_id ) ) {
		return $templates;
	}
	$assigned = get_page_template_slug( $page_id );
	if ( ! is_string( $assigned ) ) {
		return $templates;
	}
	if ( '' !== $assigned ) {
		array_unshift( $templates, $assigned );
		return $templates;
	}
	$page = get_post( $page_id );
	array_unshift( $templates, 'page-' . $page->post_name . '.php', 'page-' . $page_id . '.php', 'page.php' );
	return $templates;
}

/**
 * Use the normal Page template while its password protection is active.
 *
 * Fired by `page_template_hierarchy`.
 *
 * @internal WordPress filter hook
 * @param string[] $templates Template candidates.
 * @return string[] Template candidates.
 */
function outlet_catalog_password_template_hook( array $templates ): array {
	$page_id = filter_var( get_option( OUTLET_PAGE_OPTION ), FILTER_VALIDATE_INT );
	if ( get_queried_object_id() !== $page_id ) {
		return $templates;
	}
	if ( ! post_password_required( $page_id ) ) {
		return $templates;
	}
	$assigned = get_page_template_slug( $page_id );
	if ( ! is_string( $assigned ) ) {
		return $templates;
	}
	return array_values( array_diff( $templates, array( $assigned, $assigned . '.php' ) ) );
}

/**
 * Render Page content while restoring the product loop's post context.
 *
 * @internal
 * @param int $page_id Outlet Page ID.
 * @throws \RuntimeException When the Page no longer exists.
 */
function render_outlet_page_content( int $page_id ): string {
	$page = get_post( $page_id );
	if ( ! $page instanceof \WP_Post ) {
		throw new \RuntimeException( 'Outlet Page content could not be retrieved.' );
	}
	$context_keys     = array( 'post', 'id', 'authordata', 'currentday', 'currentmonth', 'page', 'pages', 'multipage', 'more', 'numpages', 'product' );
	$previous_context = array_intersect_key( $GLOBALS, array_flip( $context_keys ) );
	$GLOBALS['post']  = $page; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Establish the Page context only while rendering its content.
	try {
		setup_postdata( $page );
		$content = apply_filters( 'the_content', $page->post_content );
		return '<div class="wp-block-post-content alignwide outletpro-navigation">' . $content . '</div>';
	} finally {
		foreach ( $context_keys as $key ) {
			if ( array_key_exists( $key, $previous_context ) ) {
				$GLOBALS[ $key ] = $previous_context[ $key ]; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Restore the exact pre-render context, even after an exception.
			} else {
				unset( $GLOBALS[ $key ] );
			}
		}
	}
}
