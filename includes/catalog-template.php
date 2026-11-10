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
	add_action( 'after_switch_theme', 'OutletPro\\seed_outlet_template_part_hook' );
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
	$settings['templateLock']                  = 'all';
	$assigned                                  = get_page_template_slug( $page_id );
	$settings['outletproCatalogTemplateId']    = get_stylesheet() . '//' . ( $assigned ? $assigned : 'archive-product' );
	$settings['outletproOutletTemplatePartId'] = get_stylesheet() . '//outletpro-outlet-page';
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
 * Save the editable Outlet page template part for the active block theme.
 *
 * @internal
 * @param string $navigation Initial navigation blocks.
 * @throws \RuntimeException When the template part cannot be saved.
 */
function create_outlet_template_part( string $navigation ): void {
	if ( ! wp_is_block_theme() ) {
		return;
	}
	if ( get_block_template( get_stylesheet() . '//outletpro-outlet-page', 'wp_template_part' ) instanceof \WP_Block_Template ) {
		return;
	}
	$content = '<!-- wp:group {"align":"wide","className":"outletpro-navigation","layout":{"type":"constrained"}} --><div class="wp-block-group alignwide outletpro-navigation"><!-- wp:group {"align":"wide","layout":{"type":"default"}} --><div class="wp-block-group alignwide">' . $navigation . '</div><!-- /wp:group --></div><!-- /wp:group -->';
	$part_id = wp_insert_post(
		array(
			'post_type'    => 'wp_template_part',
			'post_status'  => 'publish',
			'post_name'    => 'outletpro-outlet-page',
			'post_title'   => __( 'Outlet page', 'outletpro' ),
			'post_content' => wp_slash( $content ),
		),
		true
	);
	if ( is_wp_error( $part_id ) ) {
		wc_get_logger()->error( 'Outlet template part could not be saved.' );
		throw new \RuntimeException( 'Outlet template part could not be saved.' );
	}
	$result = wp_set_object_terms( $part_id, get_stylesheet(), 'wp_theme' );
	if ( is_wp_error( $result ) ) {
		wc_get_logger()->error( 'Outlet template part theme could not be saved.' );
		throw new \RuntimeException( 'Outlet template part theme could not be saved.' );
	}
	$result = wp_set_object_terms( $part_id, WP_TEMPLATE_PART_AREA_UNCATEGORIZED, 'wp_template_part_area' );
	if ( is_wp_error( $result ) ) {
		wc_get_logger()->error( 'Outlet template part area could not be saved.' );
		throw new \RuntimeException( 'Outlet template part area could not be saved.' );
	}
}

/**
 * Seed the active theme's Outlet part after switching themes.
 *
 * Fired by `after_switch_theme`.
 *
 * @internal WordPress action hook
 */
function seed_outlet_template_part_hook(): void {
	if ( ! wp_is_block_theme() ) {
		return;
	}
	try {
		if ( null === get_catalog_page() ) {
			return;
		}
		create_outlet_template_part( get_outlet_navigation_content() );
	} catch ( \RuntimeException $e ) {
		wc_get_logger()->error( 'Outlet template part could not be initialized for the theme.' );
	} catch ( \UnexpectedValueException $e ) {
		wc_get_logger()->error( 'Outlet Page ID could not be retrieved for the theme.' );
	}
}

/**
 * Build the shared section used by Block Hooks and the manual insertion pattern.
 *
 * @internal
 */
function get_outlet_page_section_content(): string {
	$attributes = array(
		'slug'  => 'outletpro-outlet-page',
		'theme' => get_stylesheet(),
		'align' => 'wide',
	);
	return '<!-- wp:outletpro/outlet-conditional {"conditionType":"page","align":"wide","layout":{"type":"default"}} --><div class="wp-block-outletpro-outlet-conditional alignwide"><!-- wp:template-part ' . wp_json_encode( $attributes ) . ' /--></div><!-- /wp:outletpro/outlet-conditional -->';
}

/**
 * Add the Outlet section at the configured catalog anchor.
 *
 * Fired by `hooked_block_types`.
 *
 * @internal WordPress filter hook
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
 * @param string[]                      $hooked_blocks Existing hooked blocks.
 * @param string                        $relative_position Position relative to the anchor.
 * @param string                        $anchor_block Anchor block name.
 * @param \WP_Block_Template|array|null $context Template or pattern context.
 * @return string[] Hooked blocks.
 */
function auto_insert_outlet_page_hook( $hooked_blocks, $relative_position, $anchor_block, $context ): array {
	if ( ! wp_is_block_theme() ) {
		return $hooked_blocks;
	}
	$context_name = null;
	if ( $context instanceof \WP_Block_Template ) {
		$context_name = $context->slug;
	}
	if ( is_array( $context ) ) {
		$context_name = $context['name'] ?? null;
	}
	if ( ! is_string( $context_name ) ) {
		return $hooked_blocks;
	}
	if ( false === strpos( $context_name, 'archive-product' ) ) {
		return $hooked_blocks;
	}
	if ( array( $anchor_block, $relative_position ) !== get_theme_mod( OUTLET_PAGE_BLOCK_HOOK_THEME_MOD, array( 'core/term-description', 'after' ) ) ) {
		return $hooked_blocks;
	}
	$hooked_blocks[] = 'outletpro/outlet-conditional';
	return $hooked_blocks;
}

/**
 * Populate the hooked conditional with its page condition and template part.
 *
 * Fired by `hooked_block_outletpro/outlet-conditional`.
 *
 * @internal WordPress filter hook
 * @param array<string, mixed>|null $parsed_hooked_block Hooked block or suppression marker.
 * @return array<string, mixed>|null Populated block.
 */
function populate_outlet_page_section_hook( ?array $parsed_hooked_block ): ?array {
	if ( null === $parsed_hooked_block ) {
		return null;
	}
	return parse_blocks( get_outlet_page_section_content() )[0];
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
