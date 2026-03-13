<?php
/**
 * Editor guide functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Check whether the editor guide script should be enqueued for the current request.
 *
 * Returns true only when the given post ID matches the stored clearance page ID.
 *
 * @param int $current_post_id The post ID being edited.
 */
function should_enqueue_editor_guide( int $current_post_id ): bool {
	$page_id = (int) get_option( CLEARANCE_PAGE_OPTION );

	if ( $page_id <= 0 ) {
		return false;
	}

	return $current_post_id === $page_id;
}

/**
 * Enqueue the editor guide script for the clearance page.
 *
 * Fired by `enqueue_block_editor_assets`.
 *
 * @internal WordPress action hook
 */
function enqueue_editor_guide_hook(): void {
	$screen = get_current_screen();

	if ( ! $screen || ! $screen->is_block_editor() ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$current_post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;

	if ( ! should_enqueue_editor_guide( $current_post_id ) ) {
		return;
	}

	$asset_file = plugin_dir_path( __FILE__ ) . '../build/editor-guide.asset.php';

	if ( ! file_exists( $asset_file ) ) {
		return;
	}

	$asset = require $asset_file;

	wp_enqueue_script(
		'wc-clearance-editor-guide',
		plugin_dir_url( __FILE__ ) . '../build/editor-guide.js',
		$asset['dependencies'],
		$asset['version'],
		true
	);
}
