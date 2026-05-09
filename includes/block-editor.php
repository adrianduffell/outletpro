<?php
/**
 * Block editor integration functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize block editor integrations.
 *
 * @internal
 */
function init_block_editor(): void {
	add_filter( 'block_editor_settings_all', 'WC_Clearance\append_block_editor_settings_hook', 10, 2 );
	add_filter( 'allowed_block_types_all', 'WC_Clearance\restrict_clearance_blocks_to_site_editor_hook', 10, 2 );
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

/**
 * Restrict clearance blocks to the site editor inserter.
 *
 * The clearance-badge and clearance-message blocks are intended for site templates,
 * not individual pages or posts. This removes them from the inserter in all editor
 * contexts except the site editor.
 *
 * Fired by `allowed_block_types_all`.
 *
 * @internal WordPress filter hook
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint
 * @param bool|string[]            $allowed_block_types Array of block type slugs, or true to allow all, or false to allow none.
 * @param \WP_Block_Editor_Context $context             The block editor context.
 * @return bool|string[] Modified allowed block types.
 */
function restrict_clearance_blocks_to_site_editor_hook( $allowed_block_types, \WP_Block_Editor_Context $context ) {
	// Allow all blocks in the site editor (template editor).
	if ( 'core/edit-site' === $context->name ) {
		return $allowed_block_types;
	}

	$blocks_to_restrict = array(
		'wc-clearance/clearance-badge',
		'wc-clearance/clearance-message',
	);

	if ( true === $allowed_block_types ) {
		$allowed_block_types = array_keys( \WP_Block_Type_Registry::get_instance()->get_all_registered() );
	}

	if ( is_array( $allowed_block_types ) ) {
		$allowed_block_types = array_values(
			array_filter(
				$allowed_block_types,
				function ( string $block_name ) use ( $blocks_to_restrict ): bool {
					return ! in_array( $block_name, $blocks_to_restrict, true );
				}
			)
		);
	}

	return $allowed_block_types;
}
