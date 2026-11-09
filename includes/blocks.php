<?php
/**
 * Block registration and render callbacks.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize block registrations.
 *
 * @internal
 */
function init_blocks(): void {
	register_outlet_conditional_block();
	register_outlet_badge_block();
	register_outlet_message_block();
	register_outlet_link_block();
	register_outlet_tile_block();
	register_outlet_tiles_block();
	register_outlet_menu_block();
	add_filter( 'hooked_block_types', 'OutletPro\auto_insert_outlet_badge_hook', 10, 4 );
	add_filter( 'hooked_block_outletpro/outlet-badge', 'OutletPro\set_outlet_badge_spacing_hook', 10, 4 );
	add_filter( 'hooked_block_types', 'OutletPro\auto_insert_outlet_message_hook', 10, 4 );
	add_filter( 'hooked_block_outletpro/outlet-message', 'OutletPro\set_outlet_message_style_hook' );
	add_filter( 'pre_render_block', 'OutletPro\conditionally_render_outlet_conditional_hook', 10, 2 );
	add_filter( 'render_block_data', 'OutletPro\set_outlet_product_collection_orderby_hook', 11 );
	add_filter( 'query_loop_block_query_vars', 'OutletPro\filter_outlet_product_collection_hook', 11, 3 );
}

/**
 * Helper to de-initialize blocks back to the uninitialized state.
 *
 * @internal
 */
function deinit_blocks(): void {
	$registry = \WP_Block_Type_Registry::get_instance();

	remove_filter( 'pre_render_block', 'OutletPro\conditionally_render_outlet_conditional_hook' );
	remove_filter( 'hooked_block_types', 'OutletPro\auto_insert_outlet_badge_hook', 10 );
	remove_filter( 'hooked_block_outletpro/outlet-badge', 'OutletPro\set_outlet_badge_spacing_hook', 10 );
	remove_filter( 'hooked_block_types', 'OutletPro\auto_insert_outlet_message_hook', 10 );
	remove_filter( 'hooked_block_outletpro/outlet-message', 'OutletPro\set_outlet_message_style_hook' );
	remove_filter( 'render_block_data', 'OutletPro\set_outlet_product_collection_orderby_hook', 11 );
	remove_filter( 'query_loop_block_query_vars', 'OutletPro\filter_outlet_product_collection_hook', 11 );

	if ( \WP_Block_Styles_Registry::get_instance()->is_registered( 'outletpro/outlet-message', 'notice' ) ) {
		unregister_block_style( 'outletpro/outlet-message', 'notice' );
	}

	if ( \WP_Block_Styles_Registry::get_instance()->is_registered( 'outletpro/outlet-message', 'bold' ) ) {
		unregister_block_style( 'outletpro/outlet-message', 'bold' );
	}

	if ( \WP_Block_Styles_Registry::get_instance()->is_registered( 'outletpro/outlet-message', 'fine' ) ) {
		unregister_block_style( 'outletpro/outlet-message', 'fine' );
	}

	// Unregister all blocks in the outletpro namespace.
	foreach ( $registry->get_all_registered() as $block_name => $block_type ) {
		if ( 0 !== strpos( $block_name, 'outletpro/' ) ) {
			continue;
		}

		unregister_block_type( $block_name );
	}
}

/**
 * Register the outlet conditional block type.
 *
 * @internal
 */
function register_outlet_conditional_block(): void {
	register_block_type( plugin_dir_path( __DIR__ ) . 'build/blocks/outlet-conditional/' );
}

/**
 * Register the outlet badge block type.
 *
 * @internal
 */
function register_outlet_badge_block(): void {
	register_block_type(
		plugin_dir_path( __DIR__ ) . 'build/blocks/outlet-badge/',
		array(
			'render_callback' => 'OutletPro\render_outlet_badge_callback',
		)
	);
}

/**
 * Register the Outlet Tile block.
 *
 * @internal
 */
function register_outlet_tile_block(): void {
	register_block_type(
		plugin_dir_path( __DIR__ ) . 'build/blocks/outlet-tile/',
		array(
			'render_callback' => 'OutletPro\render_outlet_tile_callback',
		)
	);
}

/**
 * Render the Outlet Tile block.
 *
 * @internal
 * @param array<string, mixed> $attributes Block attributes.
 * @return string Rendered tile markup.
 */
function render_outlet_tile_callback( array $attributes ): string {
	$term_id = $attributes['category'] ?? null;
	if ( ! is_int( $term_id ) ) {
		return '';
	}
	if ( $term_id < 1 ) {
		return '';
	}
	$term = get_term( $term_id );
	if ( ! $term instanceof \WP_Term ) {
		return '';
	}

	try {
		$href = get_outlet_permalink_with_filter( $term );
	} catch ( \RuntimeException $e ) {
		\wc_get_logger()->error( 'Outlet page permalink could not be retrieved.' );
		return '';
	}
	if ( null === $href ) {
		return '';
	}

	$name                = 'product_cat' === $term->taxonomy ? $term->name : get_search_index_facet_nice_name( $term );
	$badge_markup        = '';
	$display_badge_label = $attributes['displayOutletBadgeLabel'] ?? true;
	if ( true === $display_badge_label ) {
		try {
			$badge_label = get_outlet_badge_label();
		} catch ( \UnexpectedValueException $e ) {
			\wc_get_logger()->error( 'Outlet badge label could not be retrieved.' );
			$badge_label = null;
		}
		if ( is_string( $badge_label ) ) {
			if ( '' !== $badge_label ) {
				$badge_markup = '<div class="outletpro-tile-tagline" style="font-size:0.75em">' . esc_html( $badge_label ) . '</div>';
			}
		}
	}

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'href' => esc_url( $href ),
		)
	);

	return '<a ' . $wrapper_attributes . '><div class="outletpro-tile-text">' . esc_html( $name ) . '</div>' . $badge_markup . '</a>';
}

/**
 * Register the Outlet Tiles block.
 *
 * Container for outlet tile blocks.
 *
 * @internal
 */
function register_outlet_tiles_block(): void {
	register_block_type( plugin_dir_path( __DIR__ ) . 'build/blocks/outlet-tiles/' );
}

/**
 * Register the Outlet Menu block.
 *
 * @internal
 */
function register_outlet_menu_block(): void {
	register_block_type(
		plugin_dir_path( __DIR__ ) . 'build/blocks/outlet-menu/',
		array( 'render_callback' => 'OutletPro\render_outlet_menu_callback' )
	);
}

/**
 * Render the outlet menu block.
 *
 * @internal
 * @param array<string, mixed> $attributes Block attributes.
 * @return string|null Rendered menu markup, or null when items are invalid or the outlet page is unavailable.
 */
function render_outlet_menu_callback( array $attributes ): ?string {
	$items = $attributes['items'] ?? array();
	if ( ! is_array( $items ) ) {
		return null;
	}
	if ( array_filter( $items, static fn( $id ): bool => is_int( $id ) && $id > 0 ) !== $items ) {
		return null;
	}

	try {
		$permalink = get_outlet_permalink();
	} catch ( \RuntimeException $e ) {
		return null;
	}
	if ( null === $permalink ) {
		return null;
	}

	$terms = empty( $items ) ? array() : get_terms(
		array(
			'taxonomy'   => array( 'product_cat', OUTLET_SEARCH_INDEX_TAXONOMY ),
			'include'    => $items,
			'hide_empty' => false,
			'orderby'    => 'include',
		)
	);
	if ( is_wp_error( $terms ) ) {
		return null;
	}

	$links = array();
	if ( true === ( $attributes['showAll'] ?? false ) ) {
		$links[] = array( __( 'All', 'outletpro' ), $permalink );
	}
	foreach ( $terms as $term ) {
		$url = get_outlet_permalink_with_filter( $term );
		if ( null === $url ) {
			continue;
		}
		$name = $term->name;
		if ( OUTLET_SEARCH_INDEX_TAXONOMY === $term->taxonomy ) {
			$name = get_search_index_facet_nice_name( $term );
		}
		$links[] = array( $name, $url );
	}

	$link_styles          = wp_style_engine_get_styles(
		array( 'typography' => array( 'textDecoration' => $attributes['style']['typography']['textDecoration'] ?? null ) )
	);
	$link_style_attribute = isset( $link_styles['css'] ) ? ' style="' . esc_attr( $link_styles['css'] ) . '"' : '';

	$items = array_map(
		static fn( array $link ): string => '<li><a href="' . esc_url( $link[1] ) . '"' . $link_style_attribute . '>' . esc_html( $link[0] ) . '</a></li>',
		$links
	);
	return '<menu ' . get_block_wrapper_attributes( array( 'style' => 'list-style-type:none;' ) ) . '>' . join( ' ', $items ) . '</menu>';
}

/**
 * Returns whether Block Hooks context is the single product template.
 *
 * Template slugs and pattern names containing `single-product` are recognized
 * to support Woo Purple, child themes, and other themes that use the same
 * approach.
 *
 * @internal
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
 * @param \WP_Block_Template|array|null $context Block Hooks context.
 */
function is_single_product_context( $context ): bool {
	if ( $context instanceof \WP_Block_Template ) {
		return false !== strpos( $context->slug, 'single-product' );
	}

	if ( ! is_array( $context ) ) {
		return false;
	}

	$context_name = $context['name'] ?? null;

	if ( ! is_string( $context_name ) ) {
		return false;
	}

	return false !== strpos( $context_name, 'single-product' );
}

/**
 * Auto-insert the outlet badge block on the single product template.
 *
 * Fired by `hooked_block_types`.
 *
 * @internal WordPress filter hook
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
 * @param string[]                      $hooked_blocks     Block names hooked to the anchor at this position.
 * @param string                        $relative_position Position relative to the anchor block.
 * @param string                        $anchor_block      Anchor block name.
 * @param \WP_Block_Template|array|null $context Block template or post context, or null.
 * @return string[] Filtered hooked block names.
 */
function auto_insert_outlet_badge_hook( $hooked_blocks, $relative_position, $anchor_block, $context ): array {
	if ( ! is_single_product_context( $context ) ) {
		return $hooked_blocks;
	}

	if ( array( $anchor_block, $relative_position ) !== get_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD, array( 'core/post-title', 'before' ) ) ) {
		return $hooked_blocks;
	}

	$hooked_blocks[] = 'outletpro/outlet-badge';

	return $hooked_blocks;
}

/**
 * Sets spacing for the auto-inserted outlet badge.
 *
 * Adds a small bottom margin in themes that support spacing preset 10, such
 * as Purple, to work around a parent container with a zero `blockGap`.
 *
 * Fired by `hooked_block_outletpro/outlet-badge`.
 *
 * @internal WordPress filter hook
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
 * @param array<string, mixed>|null $parsed_hooked_block Parsed outlet badge block, or null when suppressed.
 * @param string                    $hooked_block_type   Hooked block name.
 * @param string                    $relative_position   Position relative to the anchor block.
 * @param array<string, mixed>      $parsed_anchor_block Parsed anchor block.
 * @return array<string, mixed>|null Filtered outlet badge block, or null when suppressed.
 */
function set_outlet_badge_spacing_hook( $parsed_hooked_block, $hooked_block_type, $relative_position, $parsed_anchor_block ): ?array {
	if ( null === $parsed_hooked_block ) {
		return null;
	}

	if ( 'before' !== $relative_position ) {
		return $parsed_hooked_block;
	}

	if ( 'core/post-title' !== ( $parsed_anchor_block['blockName'] ?? null ) ) {
		return $parsed_hooked_block;
	}

	$parsed_hooked_block['attrs']['style']['spacing']['margin']['bottom'] = 'var:preset|spacing|10';

	return $parsed_hooked_block;
}

/**
 * Auto-insert the outlet message block on the single product template.
 *
 * Fired by `hooked_block_types`.
 *
 * @internal WordPress filter hook
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
 * @param string[]                      $hooked_blocks     Block names hooked to the anchor at this position.
 * @param string                        $relative_position Position relative to the anchor block.
 * @param string                        $anchor_block      Anchor block name.
 * @param \WP_Block_Template|array|null $context Block template or post context, or null.
 * @return string[] Filtered hooked block names.
 */
function auto_insert_outlet_message_hook( $hooked_blocks, $relative_position, $anchor_block, $context ): array {
	if ( ! is_single_product_context( $context ) ) {
		return $hooked_blocks;
	}

	if ( array( $anchor_block, $relative_position ) !== get_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-meta', 'first_child' ) ) ) {
		return $hooked_blocks;
	}

	$hooked_blocks[] = 'outletpro/outlet-message';

	return $hooked_blocks;
}

/**
 * Applies the theme mod style to auto-inserted outlet messages.
 *
 * Fired by `hooked_block_outletpro/outlet-message`.
 *
 * @internal WordPress filter hook
 * @param array<string, mixed>|null $parsed_hooked_block Parsed outlet message block, or null when suppressed.
 * @return array<string, mixed>|null Filtered outlet message block, or null when suppressed.
 * @throws \UnexpectedValueException When the message block style is not a string.
 */
function set_outlet_message_style_hook( ?array $parsed_hooked_block ): ?array {
	if ( null === $parsed_hooked_block ) {
		return null;
	}

	$style = get_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD, '' );
	if ( ! is_string( $style ) ) {
		throw new \UnexpectedValueException( 'Outlet message block style must be a string.' );
	}

	if ( '' === $style ) {
		return $parsed_hooked_block;
	}

	$parsed_hooked_block['attrs']['className'] = trim( ( $parsed_hooked_block['attrs']['className'] ?? '' ) . ' is-style-' . $style );

	return $parsed_hooked_block;
}

/**
 * Set orderby for the outlet product collection block using the URL param.
 *
 * Fired by `render_block_data`.
 *
 * @internal WordPress filter hook
 * @param array<string, mixed> $parsed_block Parsed block data.
 * @return array<string, mixed> Updated parsed block data.
 */
function set_outlet_product_collection_orderby_hook( array $parsed_block ): array {
	if ( 'woocommerce/product-collection' !== ( $parsed_block['blockName'] ?? null ) ) {
		return $parsed_block;
	}

	$is_outlet_query = $parsed_block['attrs']['query']['outletpro'] ?? false;
	if ( ! $is_outlet_query ) {
		return $parsed_block;
	}

	$orderby = sanitize_key( wp_unslash( $_GET['orderby'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only query param used to adjust catalog sort order.
	if ( ! in_array( $orderby, array( 'price', 'price-desc', 'date', 'popularity', 'rating', 'menu_order' ), true ) ) {
		return $parsed_block;
	}

	$parsed_block['attrs']['query']['orderBy'] = str_replace( '-desc', '', $orderby );
	switch ( $orderby ) {
		case 'price-desc':
		case 'date':
		case 'popularity':
		case 'rating':
			$parsed_block['attrs']['query']['order'] = 'desc';
			break;
		case 'price':
		case 'menu_order':
		default:
			$parsed_block['attrs']['query']['order'] = 'asc';
	}

	return $parsed_block;
}

/**
 * Filter the query vars for the outlet product collection block.
 *
 * Restricts the product collection query to only return products that have
 * the outlet canonical term assigned.
 *
 * Fired by `query_loop_block_query_vars`.
 *
 * @internal WordPress filter hook
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
 * @param array<string, mixed> $query   The query vars.
 * @param \WP_Block            $block   The block instance.
 * @param int                  $page    The current page.
 * @return array<string, mixed> Filtered query vars.
 */
function filter_outlet_product_collection_hook( array $query, \WP_Block $block, int $page ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	$is_outlet_query = $block->context['query']['outletpro'] ?? false;

	if ( ! $is_outlet_query ) {
		return $query;
	}

	$canonical_term = get_term_by( 'name', OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY );
	if ( ! $canonical_term ) {
		$canonical_term = 0;
	}

	if ( ! isset( $query['tax_query'] ) || ! is_array( $query['tax_query'] ) ) {
		$query['tax_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	}

	$query['tax_query'][] = array(
		'taxonomy' => OUTLET_STATUS_TAXONOMY,
		'field'    => 'term_id',
		'terms'    => $canonical_term instanceof \WP_Term ? $canonical_term->term_id : 0,
	);

	$category_filter = get_category_filter_query_param();
	if ( null !== $category_filter ) {
		$query['tax_query'][] = array(
			'taxonomy'         => 'product_cat',
			'field'            => 'slug',
			'terms'            => $category_filter,
			'include_children' => true,
		);
	}

	$price_filter = get_price_filter_query_param();
	if ( null !== $price_filter ) {
		$query['tax_query'][] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
			'field'    => 'slug',
			'terms'    => $price_filter,
		);
	}

	$discount_filter = get_discount_filter_query_param();
	if ( null !== $discount_filter ) {
		$query['tax_query'][] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			'taxonomy' => OUTLET_SEARCH_INDEX_TAXONOMY,
			'field'    => 'slug',
			'terms'    => $discount_filter,
		);
	}

	return $query;
}

/**
 * Render callback for the outlet badge block.
 *
 * @internal
 * @param array<string, mixed> $attributes Block attributes.
 * @param string               $_content   Block inner content (unused).
 * @param \WP_Block            $block      Block instance.
 * @return string Rendered HTML, or empty string if the product is not in outlet.
 */
function render_outlet_badge_callback( array $attributes, string $_content, \WP_Block $block ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	$product_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : 0;

	if ( ! $product_id ) {
		return '';
	}

	$product = wc_get_product( $product_id );

	if ( ! $product instanceof \WC_Product ) {
		return '';
	}

	if ( ! taxonomy_exists( OUTLET_STATUS_TAXONOMY ) || ! is_outlet( $product ) ) {
		return '';
	}

	$wrapper_attributes = get_block_wrapper_attributes();

	$label = get_option( OUTLET_BADGE_LABEL_OPTION );

	if ( ! is_string( $label ) || '' === $label ) {
		return '';
	}

	return sprintf(
		'<div %1$s><div class="outletpro-badge">%2$s</div></div>',
		$wrapper_attributes,
		wp_kses_post( $label )
	);
}

/**
 * Register the outlet link block type.
 *
 * @internal
 */
function register_outlet_link_block(): void {
	register_block_type(
		plugin_dir_path( __DIR__ ) . 'build/blocks/outlet-link/',
		array( 'render_callback' => 'OutletPro\render_outlet_link_callback' )
	);
}

/**
 * Render the outlet link.
 *
 * @internal
 * @param array<string, mixed> $attributes Block attributes.
 */
function render_outlet_link_callback( array $attributes ): string {
	$page_id = get_outlet_page_id();
	if ( null === $page_id ) {
		return '';
	}
	if ( ! is_post_publicly_viewable( $page_id ) ) {
		return '';
	}
	$permalink = get_outlet_permalink();
	if ( null === $permalink ) {
		return '';
	}
	$title = get_outlet_page_title();
	if ( null === $title ) {
		return '';
	}

	$text = $attributes['text'] ?? '';
	if ( '' === trim( $text ) ) {
		$title = wp_strip_all_tags( $title );
		$title = function_exists( 'mb_strtolower' ) ? mb_strtolower( $title, 'UTF-8' ) : strtolower( $title );
		$text  = sprintf(
			/* translators: %s: Lowercase outlet page title. */
			__( 'Shop all %s', 'outletpro' ),
			$title
		);
	}

	return sprintf(
		'<a %1$s href="%2$s">%3$s</a>',
		get_block_wrapper_attributes(),
		esc_url( $permalink ),
		esc_html( $text )
	);
}

/**
 * Register the outlet message block type.
 *
 * @internal
 */
function register_outlet_message_block(): void {
	register_block_type(
		plugin_dir_path( __DIR__ ) . 'build/blocks/outlet-message/',
		array(
			'render_callback' => 'OutletPro\render_outlet_message_callback',
		)
	);

	register_block_style(
		'outletpro/outlet-message',
		array(
			'name'       => 'bold',
			'label'      => __( 'Bold', 'outletpro' ),
			'style_data' => array(
				'typography' => array(
					'fontSize'   => 'var:preset|font-size|small',
					'fontWeight' => '600',
				),
			),
		)
	);

	register_block_style(
		'outletpro/outlet-message',
		array(
			'name'       => 'fine',
			'label'      => __( 'Fine', 'outletpro' ),
			'style_data' => array(
				'typography' => array(
					'fontSize' => 'var:preset|font-size|small',
				),
			),
		)
	);

	register_block_style(
		'outletpro/outlet-message',
		array(
			'name'       => 'notice',
			'label'      => __( 'Notice', 'outletpro' ),
			'style_data' => array(
				'color'   => array( 'background' => '#00000005' ),
				'spacing' => array(
					'padding' => array(
						'top'    => '1em',
						'right'  => '1em',
						'bottom' => '1em',
						'left'   => '1em',
					),
				),
			),
		)
	);
}

/**
 * Render callback for the outlet message block.
 *
 * @internal
 * @param array<string, mixed> $attributes Block attributes.
 * @param string               $_content   Block inner content (unused).
 * @param \WP_Block            $block      Block instance.
 * @return string Rendered HTML, or empty string if the product is not in outlet.
 */
function render_outlet_message_callback( array $attributes, string $_content, \WP_Block $block ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	$product_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : 0;

	if ( ! $product_id ) {
		return '';
	}

	$product = wc_get_product( $product_id );

	if ( ! $product instanceof \WC_Product ) {
		return '';
	}

	if ( ! taxonomy_exists( OUTLET_STATUS_TAXONOMY ) || ! is_outlet( $product ) ) {
		return '';
	}

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => 'outletpro-message',
		)
	);

	$message = get_option( OUTLET_MESSAGE_OPTION );

	if ( ! is_string( $message ) || '' === $message ) {
		return '';
	}

	return sprintf(
		'<p %1$s>%2$s</p>',
		$wrapper_attributes,
		wp_kses_post( $message )
	);
}

/**
 * Conditionally render Outlet Conditional block.
 *
 * The current post must be an outlet product for the block to render.
 *
 * Fired by `pre_render_block`.
 *
 * @internal WordPress filter hook
 * @param string|null          $pre_render   The pre-rendered content, or null to render normally.
 * @param array<string, mixed> $parsed_block The parsed block data.
 * @return string|null Empty string when hidden, otherwise the existing pre-rendered content.
 */
function conditionally_render_outlet_conditional_hook( ?string $pre_render, array $parsed_block ): ?string {
	if ( 'outletpro/outlet-conditional' !== ( $parsed_block['blockName'] ?? null ) ) {
		return $pre_render;
	}

	$product = wc_get_product( get_the_ID() );
	if ( ! $product instanceof \WC_Product ) {
		return '';
	}

	try {
		if ( ! is_outlet( $product ) ) {
			return '';
		}
	} catch ( \Throwable $e ) {
		wc_get_logger()->error( 'Outlet product status could not be determined in outlet conditional block.' );
		return '';
	}

	return $pre_render;
}
