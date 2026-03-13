<?php
/**
 * Setup task functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Admin\Features\OnboardingTasks\TaskLists;

/**
 * Register the clearance page setup task with WooCommerce and the page lifecycle hook.
 *
 * @since 1.0.0
 * @throws \RuntimeException If the WooCommerce TaskLists class is not available.
 */
function init_setup_task(): void {
	if ( ! class_exists( 'Automattic\WooCommerce\Admin\Features\OnboardingTasks\TaskLists' ) ) {
		throw new \RuntimeException( 'WooCommerce TaskLists class not found. This plugin requires WooCommerce to be active.' );
	}

	TaskLists::add_task( 'extended', new Publish_Clearance_Page_Task() );
	add_action( 'transition_post_status', 'WC_Clearance\mark_clearance_page_task_complete_hook', 10, 3 );
}

/**
 * Mark the clearance section page task complete when the page is published.
 *
 * Fired by `transition_post_status`.
 *
 * @internal WordPress action hook
 *
 * @param string   $new_status New post status.
 * @param string   $old_status Previous post status.
 * @param \WP_Post $post       Post object.
 */
function mark_clearance_page_task_complete_hook( string $new_status, string $old_status, \WP_Post $post ): void {
	if ( 'publish' !== $new_status ) {
		return;
	}

	if ( (int) get_option( CLEARANCE_PAGE_OPTION ) !== $post->ID ) {
		return;
	}

	$task = new Publish_Clearance_Page_Task();
	$task->mark_actioned();
}
