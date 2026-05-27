<?php
/**
 * Block registration and render callbacks.
 *
 * @package WC_Outlet
 */

namespace WC_Outlet;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize block registrations.
 *
 * @internal
 */
function init_blocks(): void {
	register_outlet_badge_block();
	register_outlet_message_block();
	add_filter( 'hooked_block_types', 'WC_Outlet\auto_insert_outlet_badge_hook', 10, 4 );
	add_filter( 'hooked_block_types', 'WC_Outlet\auto_insert_outlet_message_hook', 10, 4 );
	add_filter( 'query_loop_block_query_vars', 'WC_Outlet\filter_outlet_product_collection_hook', 11, 3 );
}

/**
 * Helper to de-initialize blocks back to the uninitialized state.
 *
 * @internal
 */
function deinit_blocks(): void {
	$registry = \WP_Block_Type_Registry::get_instance();

	// Unregister all blocks in the wc-outlet namespace.
	foreach ( $registry->get_all_registered() as $block_name => $block_type ) {
		if ( 0 !== strpos( $block_name, 'wc-outlet/' ) ) {
			continue;
		}

		unregister_block_type( $block_name );
	}
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
			'render_callback' => 'WC_Outlet\render_outlet_badge_callback',
		)
	);
}

/**
 * Auto-insert the outlet badge block after the product price on the single product template.
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
	if ( 'woocommerce/product-price' !== $anchor_block || 'after' !== $relative_position ) {
		return $hooked_blocks;
	}

	// Only auto-insert the badge on the single product template.
	if ( $context instanceof \WP_Block_Template && 'single-product' === $context->slug ) {
		$hooked_blocks[] = 'wc-outlet/outlet-badge';
	}

	return $hooked_blocks;
}

/**
 * Auto-insert the outlet message block as the first child of the product meta block on the single product template.
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
	if ( 'woocommerce/product-meta' !== $anchor_block || 'first_child' !== $relative_position ) {
		return $hooked_blocks;
	}

	// Only auto-insert the message on the single product template.
	if ( $context instanceof \WP_Block_Template && 'single-product' === $context->slug ) {
		$hooked_blocks[] = 'wc-outlet/outlet-message';
	}

	return $hooked_blocks;
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
	$is_outlet_query = $block->context['query']['wc_outlet'] ?? false;

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

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => 'wc-outlet-badge',
		)
	);

	$label = get_option( OUTLET_BADGE_LABEL_OPTION );

	if ( ! is_string( $label ) || '' === $label ) {
		return '';
	}

	return sprintf(
		'<div %1$s>%2$s</div>',
		$wrapper_attributes,
		wp_kses_post( $label )
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
			'render_callback' => 'WC_Outlet\render_outlet_message_callback',
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
			'class' => 'wc-outlet-message',
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
