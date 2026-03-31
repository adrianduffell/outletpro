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
 * @since 1.0.0
 */
function block_editor_init(): void {
	add_filter( 'block_editor_settings_all', 'WC_Clearance\append_block_editor_settings_hook' );
}

/**
 * Append the clearance canonical term ID to the block editor settings.
 *
 * @internal WordPress filter hook
 * @param array<string, mixed> $settings Existing block editor settings.
 * @return array<string, mixed> Modified block editor settings.
 */
function append_block_editor_settings_hook( array $settings ): array {
	$canonical_term = get_term_by( 'name', CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

	if ( ! $canonical_term instanceof \WP_Term ) {
		return $settings;
	}

	$settings['wcClearance'] = array_merge(
		isset( $settings['wcClearance'] ) && is_array( $settings['wcClearance'] ) ? $settings['wcClearance'] : array(),
		array( 'clearanceTermId' => $canonical_term->term_id )
	);

	return $settings;
}
