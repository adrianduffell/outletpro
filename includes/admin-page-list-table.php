<?php
/**
 * Admin page list table functions.
 *
 * @package WC_Outlet
 */

namespace WC_Outlet;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize admin page list table features.
 *
 * @internal
 */
function init_admin_page_list_table(): void {
	add_filter( 'display_post_states', 'WC_Outlet\outlet_page_label_hook', 10, 2 );
}

/**
 * Add a "Outlet Page" label to the outlet page in the admin listing table.
 *
 * Fired by `display_post_states`.
 *
 * @param string[] $post_states An array of post display states.
 * @param \WP_Post $post        The current post object.
 * @return string[] Modified post display states.
 * @internal WordPress filter
 */
function outlet_page_label_hook( array $post_states, \WP_Post $post ): array {
	try {
		$page_id = get_outlet_page_id();
	} catch ( \UnexpectedValueException $e ) {
		return $post_states;
	}

	if ( null === $page_id ) {
		return $post_states;
	}

	if ( $post->ID === $page_id ) {
		$post_states['wc_outlet_page'] = __( 'Outlet Page', 'outletpro' );
	}

	return $post_states;
}
