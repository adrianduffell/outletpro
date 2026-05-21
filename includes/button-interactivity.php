<?php
/**
 * Core button interactivity integration.
 *
 * @package WC_Outlet
 */

namespace WC_Outlet;

defined( 'ABSPATH' ) || exit;

/**
 * Interactivity store namespace for enhanced core/button blocks.
 *
 * @internal
 */
const BUTTON_INTERACTIVITY_NAMESPACE = 'wc-outlet/button-interactivity';

/**
 * Interactivity module identifier for enhanced core/button blocks.
 *
 * @internal
 */
const BUTTON_INTERACTIVITY_MODULE_ID = '@wc-outlet/button-interactivity';

/**
 * Initialize core button interactivity integration.
 *
 * @internal
 */
function init_button_interactivity(): void {
	add_filter( 'render_block', 'WC_Outlet\inject_button_interactivity_attributes_hook', 10, 2 );
}

/**
 * Inject Interactivity API attributes into rendered core/button blocks.
 *
 * Fired by `render_block`.
 *
 * @internal WordPress filter hook
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
 * @param string               $block_content Rendered block HTML.
 * @param array<string, mixed> $block         Parsed block data.
 * @return string Filtered block HTML.
 */
function inject_button_interactivity_attributes_hook( string $block_content, array $block ): string {
	if ( 'core/button' !== ( $block['blockName'] ?? '' ) ) {
		return $block_content;
	}

	if ( '' === $block_content || ! class_exists( 'WP_HTML_Tag_Processor' ) || ! function_exists( 'wp_enqueue_script_module' ) ) {
		return $block_content;
	}

	wp_enqueue_script_module(
		BUTTON_INTERACTIVITY_MODULE_ID,
		plugin_dir_url( PLUGIN_FILE ) . 'assets/js/button-interactivity.js'
	);

	$processor = new \WP_HTML_Tag_Processor( $block_content );
	if ( ! $processor->next_tag() ) {
		return $block_content;
	}

	$processor->set_attribute( 'data-wp-interactive', BUTTON_INTERACTIVITY_NAMESPACE );
	$processor->set_attribute( 'data-wp-on--click', 'actions.logHello' );

	return $processor->get_updated_html();
}
