<?php
/**
 * Block editor hooks and settings.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize block editor integrations.
 *
 * @since 1.0.0
 */
function block_editor_init(): void {
	add_filter( 'block_editor_settings_all', 'WC_Clearance\append_block_editor_settings_hook', 10, 2 );
	add_filter( 'hooked_block_types', 'WC_Clearance\hooked_clearance_badge_hook', 10, 4 );
}

/**
 * Auto-insert the clearance badge block after the product price in product templates.
 *
 * @internal WordPress filter hook
 * @param array<int, string>                       $hooked_blocks     Blocks to insert.
 * @param string                                   $relative_position Position relative to anchor block.
 * @param string|null                              $anchor_block      Anchor block name, or null when there is no anchor.
 * @param \WP_Block_Template|\WP_Post|array<mixed> $_context     Template context.
 * @return array<int, string> Modified hooked blocks.
 */
function hooked_clearance_badge_hook( array $hooked_blocks, string $relative_position, ?string $anchor_block, \WP_Block_Template|\WP_Post|array $_context ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	if ( 'woocommerce/product-price' === $anchor_block && 'after' === $relative_position ) {
		$hooked_blocks[] = 'wc-clearance/clearance-badge';
	}

	return $hooked_blocks;
}

/**
 * Append the clearance canonical term ID to the block editor settings.
 *
 * @internal WordPress filter hook
 * @param array<string, mixed>     $settings Existing block editor settings.
 * @param \WP_Block_Editor_Context $_context The block editor context.
 * @return array<string, mixed> Modified block editor settings.
 */
function append_block_editor_settings_hook( array $settings, \WP_Block_Editor_Context $_context ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	$canonical_term = get_term_by( 'name', CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

	if ( ! $canonical_term instanceof \WP_Term ) {
		return $settings;
	}

	$settings['wcClearanceCanonicalTermId'] = $canonical_term->term_id;

	return $settings;
}
