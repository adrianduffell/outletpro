<?php
/**
 * Block registration and render callbacks.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize block registrations.
 *
 * @since 1.0.0
 */
function blocks_init(): void {
	register_clearance_badge_block();
	register_clearance_message_block();
}

/**
 * Helper to unregister block registrations.
 *
 * @since 1.0.0
 */
function reset_blocks(): void {
	$registry = \WP_Block_Type_Registry::get_instance();

	$blocks = array_filter(
		array(
			'wc-clearance/clearance-badge',
			'wc-clearance/clearance-message',
		),
		array( $registry, 'is_registered' )
	);

	array_map( 'unregister_block_type', $blocks );
}

/**
 * Register the clearance badge block type.
 *
 * @since 1.0.0
 */
function register_clearance_badge_block(): void {
	register_block_type(
		plugin_dir_path( __DIR__ ) . 'src/blocks/clearance-badge/',
		array(
			'render_callback' => 'WC_Clearance\render_clearance_badge_callback',
		)
	);
}

/**
 * Render callback for the clearance badge block.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @param string               $_content   Block inner content (unused).
 * @param \WP_Block            $block      Block instance.
 * @return string Rendered HTML, or empty string if the product is not in clearance.
 */
function render_clearance_badge_callback( array $attributes, string $_content, \WP_Block $block ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	$product_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : 0;

	if ( ! $product_id ) {
		return '';
	}

	$product = wc_get_product( $product_id );

	if ( ! $product instanceof \WC_Product ) {
		return '';
	}

	if ( ! taxonomy_exists( CLEARANCE_STATUS_TAXONOMY ) || ! is_clearance( $product ) ) {
		return '';
	}

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => 'wc-clearance-badge',
			'style' => 'display:inline-block; border-radius:4px;',
		)
	);

	return sprintf(
		'<span %1$s>%2$s</span>',
		$wrapper_attributes,
		wp_kses_post( $attributes['label'] ?? '' )
	);
}

/**
 * Register the clearance message block type.
 *
 * @since 1.0.0
 */
function register_clearance_message_block(): void {
	register_block_type(
		plugin_dir_path( __DIR__ ) . 'src/blocks/clearance-message/',
		array(
			'render_callback' => 'WC_Clearance\render_clearance_message_callback',
		)
	);
}

/**
 * Render callback for the clearance message block.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @param string               $_content   Block inner content (unused).
 * @param \WP_Block            $block      Block instance.
 * @return string Rendered HTML, or empty string if the product is not in clearance.
 */
function render_clearance_message_callback( array $attributes, string $_content, \WP_Block $block ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	$product_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : 0;

	if ( ! $product_id ) {
		return '';
	}

	$product = wc_get_product( $product_id );

	if ( ! $product instanceof \WC_Product ) {
		return '';
	}

	if ( ! taxonomy_exists( CLEARANCE_STATUS_TAXONOMY ) || ! is_clearance( $product ) ) {
		return '';
	}

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => 'wc-clearance-message',
		)
	);

	return sprintf(
		'<p %1$s>%2$s</p>',
		$wrapper_attributes,
		wp_kses_post( $attributes['message'] ?? '' )
	);
}
