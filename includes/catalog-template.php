<?php
/**
 * Shared catalog templates with editable outlet navigation.
 *
 * @package OutletPro
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Register template and Page-content integrations.
 *
 * @internal
 */
function init_catalog_templates(): void {
	add_filter( 'archive_template_hierarchy', 'OutletPro\\outlet_catalog_hierarchy_hook' );
	add_filter( 'page_template_hierarchy', 'OutletPro\\outlet_catalog_password_template_hook' );
	add_filter( 'pre_render_block', 'OutletPro\\outlet_page_content_hook', 10, 2 );
	add_filter( 'block_editor_settings_all', 'OutletPro\\outlet_catalog_editor_settings_hook', 10, 2 );
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
 * @return string|null Template slug, or null for classic themes.
 * @throws \RuntimeException When the template cannot be saved.
 */
function create_outlet_catalog_template(): ?string {
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
			'post_content' => wp_slash( compose_outlet_catalog_content( $shop->content ) ),
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
 * @param bool   $preview Whether to use an independent editor product query.
 */
function compose_outlet_catalog_content( string $content, bool $preview = false ): string {
	$blocks  = prepare_outlet_catalog_blocks( resolve_pattern_blocks( parse_blocks( $content ) ), $preview );
	$content = serialize_blocks( $blocks );
	$heading = find_catalog_heading( $blocks );
	$region  = '<!-- wp:post-content {"align":"wide","outletproPageContent":true} /-->';
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
 * Prepare inherited collections for outlet-only editor previews.
 *
 * @internal
 * @param array<array<string, mixed>> $blocks Parsed template blocks.
 * @param bool                        $preview Whether this is an editor preview.
 * @return array<array<string, mixed>> Prepared blocks.
 */
function prepare_outlet_catalog_blocks( array $blocks, bool $preview ): array {
	foreach ( $blocks as &$block ) {
		if ( $preview && 'core/query-title' === $block['blockName'] ) {
			$block['blockName']                      = 'core/post-title';
			$block['attrs']['level']                 = 1;
			$block['attrs']['outletproArchiveTitle'] = true;
			unset( $block['attrs']['type'], $block['attrs']['showPrefix'] );
		}
		if ( 'woocommerce/product-collection' === $block['blockName'] && ! empty( $block['attrs']['query']['inherit'] ) ) {
			if ( $preview ) {
				$block['attrs']['query']['inherit']   = false;
				$block['attrs']['query']['outletpro'] = true;
			}
		}
		$block['innerBlocks'] = prepare_outlet_catalog_blocks( $block['innerBlocks'], $preview );
	}
	return $blocks;
}

/**
 * Find the archive heading without selecting individual product titles.
 *
 * @internal
 * @param array<array<string, mixed>> $blocks Parsed template blocks.
 */
function find_catalog_heading( array $blocks ): ?string {
	foreach ( $blocks as $block ) {
		if ( ! empty( $block['attrs']['outletproArchiveTitle'] ) ) {
			return serialize_block( $block );
		}
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
 * Select the composed block template for outlet product archives.
 *
 * Fired by `archive_template_hierarchy`.
 *
 * @internal WordPress filter hook
 * @param string[] $templates Template candidates.
 * @return string[] Template candidates.
 */
function outlet_catalog_hierarchy_hook( array $templates ): array {
	if ( wp_is_block_theme() && get_query_var( 'outletpro_catalog' ) ) {
		array_unshift( $templates, 'outlet-catalog.php' );
	}
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
	if ( ! post_password_required() ) {
		return $templates;
	}
	return array_values( array_diff( $templates, array( 'outlet-catalog.php' ) ) );
}

/**
 * Render the explicitly marked navigation block using the Page's content.
 *
 * Fired by `pre_render_block`.
 *
 * @internal WordPress filter hook
 * @param string|null         $content Earlier rendered output.
 * @param array<string,mixed> $block Parsed block.
 */
function outlet_page_content_hook( ?string $content, array $block ): ?string {
	if ( 'core/post-content' !== $block['blockName'] ) {
		return $content;
	}
	if ( empty( $block['attrs']['outletproPageContent'] ) ) {
		return $content;
	}
	try {
		$page = get_catalog_page();
		if ( null === $page ) {
			return $content;
		}
		if ( get_query_var( 'outletpro_catalog' ) !== $page->ID ) {
			return $content;
		}
		return render_outlet_page_content( $page->ID );
	} catch ( \RuntimeException $e ) {
		wc_get_logger()->error( 'Outlet navigation could not be rendered.' );
		return '';
	}
}

/**
 * Render navigation while restoring the product loop's post context.
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

/**
 * Supply scoped editor configuration for the Outlet Page.
 *
 * Fired by `block_editor_settings_all`.
 *
 * @internal WordPress filter hook
 * @param array<string,mixed>      $settings Editor settings.
 * @param \WP_Block_Editor_Context $context Editor context.
 * @return array<string,mixed> Editor settings.
 */
function outlet_catalog_editor_settings_hook( array $settings, \WP_Block_Editor_Context $context ): array {
	if ( ! isset( $context->post ) ) {
		return $settings;
	}
	try {
		if ( get_outlet_page_id() !== $context->post->ID ) {
			return $settings;
		}
		$settings['outletproCatalogPage'] = true;
		if ( wp_is_block_theme() ) {
			$template = get_block_template( get_stylesheet() . '//outlet-catalog' );
			if ( $template instanceof \WP_Block_Template ) {
				$settings['outletproCatalogTemplateId'] = $template->id;
				$settings['outletproCatalogPreview']    = serialize_blocks( prepare_outlet_catalog_blocks( parse_blocks( $template->content ), true ) );
			}
		}
	} catch ( \RuntimeException $e ) {
		wc_get_logger()->error( 'Outlet catalog editor settings could not be retrieved.' );
	}
	return $settings;
}
