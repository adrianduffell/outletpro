<?php
/**
 * Admin page list table functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize admin page list table features.
 *
 * @since 1.0.0
 */
function init_admin_page_list_table(): void {
	add_filter( 'display_post_states', 'WC_Clearance\clearance_section_label_hook', 10, 2 );
}

/**
 * Add a "Clearance Section Page" label to the clearance page in the admin listing table.
 *
 * Fired by `display_post_states`.
 *
 * @param string[] $post_states An array of post display states.
 * @param \WP_Post $post        The current post object.
 * @return string[] Modified post display states.
 * @internal WordPress filter
 */
function clearance_section_label_hook( array $post_states, \WP_Post $post ): array {
	$page_id = get_option( CLEARANCE_PAGE_OPTION );
	if ( ! is_int( $page_id ) ) {
		return $post_states;
	}

	if ( $page_id > 0 && $post->ID === $page_id ) {
		$post_states['wc_clearance_page'] = __( 'Clearance Section Page', 'wc-clearance' );
	}

	return $post_states;
}
