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

	$badge_text      = $attributes['badgeText'] ?? __( 'Clearance', 'wc-clearance' );
	$raw_badge_color = $attributes['badgeColor'] ?? '';
	$badge_color     = sanitize_hex_color( $raw_badge_color );
	if ( ! $badge_color ) {
		$badge_color = '#2145e6';
	}
	$style = sprintf(
		'background-color:%s;color:#ffffff;padding:4px 12px;border-radius:4px;display:inline-block;font-size:0.875rem;font-weight:600;',
		$badge_color
	);

	return sprintf(
		'<span class="wc-clearance-badge" style="%s">%s</span>',
		esc_attr( $style ),
		esc_html( $badge_text )
	);
}
