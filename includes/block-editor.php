<?php
/**
 * Block editor integration functions.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
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
 * Append data to the block editor settings.
 *
 * - outletproIsBlockTheme: boolean indicating if the current theme is a block theme.
 * - outletproCartUrl: URL of the WooCommerce cart page.
 * - wcOutletCanonicalTermId: ID of the canonical term for the outlet status taxonomy.
 *
 * Fired by `block_editor_settings_all`.
 *
 * @internal WordPress filter hook
 *
 * @param array<string, mixed>     $settings Existing block editor settings.
 * @param \WP_Block_Editor_Context $_context The block editor context.
 * @return array<string, mixed> Modified block editor settings.
 */
function append_block_editor_settings_hook( array $settings, \WP_Block_Editor_Context $_context ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	$settings['outletproIsBlockTheme'] = wp_is_block_theme();
	$settings['outletproCartUrl']      = wc_get_cart_url();

	$canonical_term = get_term_by( 'name', OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY );

	if ( ! $canonical_term instanceof \WP_Term ) {
		return $settings;
	}

	$settings['wcOutletCanonicalTermId'] = $canonical_term->term_id;

	return $settings;
}
