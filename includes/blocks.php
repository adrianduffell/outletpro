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
 * @internal
 */
function init_blocks(): void {
	register_clearance_badge_block();
	register_clearance_message_block();
	add_filter( 'hooked_block_types', 'WC_Clearance\auto_insert_clearance_badge_hook', 10, 4 );
	add_filter( 'hooked_block_types', 'WC_Clearance\auto_insert_clearance_message_hook', 10, 4 );
}

/**
 * Helper to de-initialize blocks back to the uninitialized state.
 *
 * @internal
 */
function deinit_blocks(): void {
	$registry = \WP_Block_Type_Registry::get_instance();

	// Unregister all blocks in the wc-clearance namespace.
	foreach ( $registry->get_all_registered() as $block_name => $block_type ) {
		if ( 0 !== strpos( $block_name, 'wc-clearance/' ) ) {
			continue;
		}

		unregister_block_type( $block_name );
	}
}

/**
 * Register the clearance badge block type.
 *
 * @internal
 */
function register_clearance_badge_block(): void {
	register_block_type(
		plugin_dir_path( __DIR__ ) . 'build/blocks/clearance-badge/',
		array(
			'render_callback' => 'WC_Clearance\render_clearance_badge_callback',
		)
	);
}

/**
 * Auto-insert the clearance badge block after the product price on the single product template.
 *
 * @internal WordPress filter hook
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
 * @param string[]                      $hooked_blocks     Block names hooked to the anchor at this position.
 * @param string                        $relative_position Position relative to the anchor block.
 * @param string                        $anchor_block      Anchor block name.
 * @param \WP_Block_Template|array|null $context Block template or post context, or null.
 * @return string[] Filtered hooked block names.
 */
function auto_insert_clearance_badge_hook( $hooked_blocks, $relative_position, $anchor_block, $context ): array {
	if ( 'woocommerce/product-price' !== $anchor_block || 'after' !== $relative_position ) {
		return $hooked_blocks;
	}

	// Only auto-insert the badge on the single product template.
	if ( $context instanceof \WP_Block_Template && 'single-product' === $context->slug ) {
		$hooked_blocks[] = 'wc-clearance/clearance-badge';
	}

	return $hooked_blocks;
}

/**
 * Auto-insert the clearance message block as the first child of the product meta block on the single product template.
 *
 * @internal WordPress filter hook
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
 * @param string[]                      $hooked_blocks     Block names hooked to the anchor at this position.
 * @param string                        $relative_position Position relative to the anchor block.
 * @param string                        $anchor_block      Anchor block name.
 * @param \WP_Block_Template|array|null $context Block template or post context, or null.
 * @return string[] Filtered hooked block names.
 */
function auto_insert_clearance_message_hook( $hooked_blocks, $relative_position, $anchor_block, $context ): array {
	if ( 'woocommerce/product-meta' !== $anchor_block || 'first_child' !== $relative_position ) {
		return $hooked_blocks;
	}

	// Only auto-insert the message on the single product template.
	if ( $context instanceof \WP_Block_Template && 'single-product' === $context->slug ) {
		$hooked_blocks[] = 'wc-clearance/clearance-message';
	}

	return $hooked_blocks;
}

/**
 * Render callback for the clearance badge block.
 *
 * @internal
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

	$bg_color   = sanitize_hex_color( get_option( CLEARANCE_BADGE_BG_COLOR_OPTION, CLEARANCE_BADGE_BG_COLOUR_DEFAULT ) ) ?? CLEARANCE_BADGE_BG_COLOUR_DEFAULT;
	$text_color = sanitize_hex_color( get_option( CLEARANCE_BADGE_TEXT_COLOR_OPTION, CLEARANCE_BADGE_TEXT_COLOUR_DEFAULT ) ) ?? CLEARANCE_BADGE_TEXT_COLOUR_DEFAULT;

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => 'wc-clearance-badge',
			'style' => "background-color:{$bg_color};color:{$text_color};",
		)
	);

	$label = get_option( CLEARANCE_BADGE_LABEL_OPTION );

	if ( ! is_string( $label ) || '' === $label ) {
		$label = __( 'Clearance', 'wc-clearance' );
	}

	return sprintf(
		'<span %1$s>%2$s</span>',
		$wrapper_attributes,
		wp_kses_post( $label )
	);
}

/**
 * Register the clearance message block type.
 *
 * @internal
 */
function register_clearance_message_block(): void {
	register_block_type(
		plugin_dir_path( __DIR__ ) . 'build/blocks/clearance-message/',
		array(
			'render_callback' => 'WC_Clearance\render_clearance_message_callback',
		)
	);
}

/**
 * Render callback for the clearance message block.
 *
 * @internal
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

	$message = get_option( CLEARANCE_MESSAGE_OPTION );

	if ( ! is_string( $message ) || '' === $message ) {
		$message = __( 'Not eligible for change of mind returns', 'wc-clearance' );
	}

	return sprintf(
		'<p %1$s>%2$s</p>',
		$wrapper_attributes,
		wp_kses_post( $message )
	);
}
