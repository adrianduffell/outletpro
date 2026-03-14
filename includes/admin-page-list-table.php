<?php
/**
 * Admin page list table functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

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
	$page_id = (int) get_option( CLEARANCE_PAGE_OPTION );

	if ( $page_id > 0 && $post->ID === $page_id ) {
		$post_states['wc_clearance_page'] = __( 'Clearance Section Page', 'wc-clearance' );
	}

	return $post_states;
}
