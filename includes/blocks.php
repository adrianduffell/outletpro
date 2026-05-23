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
	add_filter( 'render_block_core/buttons', 'WC_Outlet\filter_tiles_active_class_hook', 10, 2 );
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
 * Add an active class to filter tile links whose href exactly matches the current URL.
 *
 * Fired by `render_block_core/buttons`.
 *
 * @internal WordPress filter hook
 * @param string               $block_content Rendered block content.
 * @param array<string, mixed> $block         Parsed block.
 * @return string Filtered block content.
 */
function filter_tiles_active_class_hook( string $block_content, array $block ): string {
	$attributes = is_array( $block['attrs'] ?? null ) ? $block['attrs'] : array();
	$class_name = $attributes['className'] ?? '';

	if (
		! is_string( $class_name ) ||
		! in_array( 'wc-outlet-filter-tiles', preg_split( '/\s+/', trim( $class_name ) ), true )
	) {
		return $block_content;
	}

	$current_url = home_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$processor   = new \WP_HTML_Tag_Processor( $block_content );
	$matched     = false;

	while ( $processor->next_tag( 'a' ) ) {
		$href = $processor->get_attribute( 'href' );

		if ( ! is_string( $href ) ) {
			continue;
		}

		if ( $current_url !== $href ) {
			continue;
		}

		$processor->add_class( 'wc-outlet-is-active' );
		$matched = true;
	}

	if ( $matched && ! wp_style_is( 'wc-outlet-filter-tiles', 'enqueued' ) ) {
		enqueue_filter_tiles_style();
	}

	return $matched ? $processor->get_updated_html() : $block_content;
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
