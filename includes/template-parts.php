<?php
/**
 * Template-parts functions.
 *
 * @package OutletPro
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Initialize template-parts.
 *
 * @internal
 */
function init_template_parts(): void {
	add_filter( 'get_block_templates', 'OutletPro\\filter_block_templates', 10, 3 );
	add_filter( 'get_block_file_template', 'OutletPro\\outlet_hero_file_template_hook', 10, 3 );
}

/**
 * De-Initialize template-parts.
 *
 * @internal
 */
function deinit_template_parts(): void {
	remove_filter( 'get_block_templates', 'OutletPro\\filter_block_templates' );
	remove_filter( 'get_block_file_template', 'OutletPro\\outlet_hero_file_template_hook' );
}

/**
 * Adds the outlet page hero template part to the available block templates.
 *
 * Fired by `get_block_templates`.
 *
 * @internal WordPress filter hook
 * @param \WP_Block_Template[] $templates Native template results.
 * @param array<string, mixed> $query Template query constraints.
 * @param string               $template_type Queried template type.
 * @return \WP_Block_Template[] Template results including the default hero.
 */
function filter_block_templates( array $templates, array $query, string $template_type ): array {
	if ( 'wp_template_part' !== $template_type ) {
		return $templates;
	}
	if ( ! wp_is_block_theme() ) {
		return $templates;
	}
	if ( isset( $query['wp_id'] ) ) {
		return $templates;
	}
	if ( isset( $query['area'] ) && WP_TEMPLATE_PART_AREA_UNCATEGORIZED !== $query['area'] ) {
		return $templates;
	}
	if ( ! empty( $query['slug__in'] ) && ! in_array( 'outlet-hero', $query['slug__in'], true ) ) {
		return $templates;
	}
	foreach ( $templates as $template ) {
		if ( 'outlet-hero' === $template->slug ) {
			return $templates;
		}
	}
	$part = get_block_template( get_stylesheet() . '//outlet-hero', 'wp_template_part' );
	if ( null === $part ) {
		return $templates;
	}
	if ( isset( $query['area'] ) && $query['area'] !== $part->area ) {
		return $templates;
	}
	$templates[] = $part;
	return $templates;
}

/**
 * Supply the hero only when WordPress has no saved or theme-provided part.
 *
 * Fired by `get_block_file_template`.
 *
 * @internal WordPress filter hook
 * @param \WP_Block_Template|null $template Native fallback template.
 * @param string                  $id Requested theme and template slug.
 * @param string                  $template_type Requested template type.
 * @return \WP_Block_Template|null Resolved template part or native result.
 */
function outlet_hero_file_template_hook( ?\WP_Block_Template $template, string $id, string $template_type ): ?\WP_Block_Template {
	if ( null !== $template ) {
		return $template;
	}
	if ( 'wp_template_part' !== $template_type ) {
		return null;
	}
	if ( ! wp_is_block_theme() ) {
		return null;
	}
	if ( get_stylesheet() . '//outlet-hero' !== $id ) {
		return null;
	}
	// Use select_outlet_navigation_pattern() once it is available on main.
	$pattern = 'outletpro/outlet-navigation-3';
	if ( ! \WP_Block_Patterns_Registry::get_instance()->is_registered( $pattern ) ) {
		wc_get_logger()->error( 'Outlet hero default could not be retrieved.' );
		return null;
	}
	$navigation = get_comment_delimited_block_content( 'core/pattern', array( 'slug' => $pattern ), '' );

	$part                 = new \WP_Block_Template();
	$part->id             = get_stylesheet() . '//outlet-hero';
	$part->theme          = get_stylesheet();
	$part->slug           = 'outlet-hero';
	$part->type           = 'wp_template_part';
	$part->title          = __( 'Outlet page hero', 'outletpro' );
	$part->description    = __( 'Hero section for the outlet page.', 'outletpro' );
	$part->source         = 'plugin';
	$part->origin         = 'plugin';
	$part->plugin         = 'outletpro';
	$part->status         = 'publish';
	$part->area           = WP_TEMPLATE_PART_AREA_UNCATEGORIZED;
	$part->has_theme_file = false;
	$part->content        = $navigation;

	return $part;
}
