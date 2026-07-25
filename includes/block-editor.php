<?php
/**
 * Block editor integration functions.
 *
 * @package OutletPro
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize block editor integrations.
 *
 * @internal
 */
function init_block_editor(): void {
	add_filter( 'block_editor_settings_all', 'OutletPro\append_block_editor_settings_hook', 10, 2 );
}

/**
 * Append the outlet canonical term ID to the block editor settings.
 *
 * @internal WordPress filter hook
 * @param array<string, mixed>     $settings Existing block editor settings.
 * @param \WP_Block_Editor_Context $_context The block editor context.
 * @return array<string, mixed> Modified block editor settings.
 */
function append_block_editor_settings_hook( array $settings, \WP_Block_Editor_Context $_context ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	$canonical_term = get_term_by( 'name', OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY );

	if ( ! $canonical_term instanceof \WP_Term ) {
		return $settings;
	}

	$settings['wcOutletCanonicalTermId'] = $canonical_term->term_id;

	return $settings;
}
