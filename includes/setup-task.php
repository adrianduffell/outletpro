<?php
/**
 * Setup task functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Admin\Features\OnboardingTasks\Task;
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

	TaskLists::add_task(
		'extended',
		new class( TaskLists::get_list( 'extended' ) ) extends Task {
			public function get_id(): string {
				return 'publish-clearance-page5';
			}

			public function get_title(): string {
				return __( 'Publish the clearance section page', 'wc-clearance' );
			}

			public function get_content(): string {
				return __( 'Publish the clearance section page to make it visible to your customers.', 'wc-clearance' );
			}

			public function get_time(): string {
				return '';
			}

			public function get_action_label(): string {
				return __( 'Publish page', 'wc-clearance' );
			}

			public function get_action_url(): string {
				return setup_task_action_url();
			}

			public function can_view(): bool {
				return setup_task_can_view();
			}

			public function is_complete(): bool {
				return setup_task_is_complete();
			}
		}
	);

	add_action( 'transition_post_status', 'WC_Clearance\mark_clearance_page_task_complete_hook', 10, 3 );
}

/**
 * Returns the URL for the clearance page setup task action button.
 */
function setup_task_action_url(): string {
	try {
		$page_id = get_clearance_page_id();
	} catch ( \Throwable $e ) {
		return '';
	}

	if ( is_null( $page_id ) ) {
		return '';
	}

	return admin_url( 'post.php?post=' . $page_id . '&action=edit' );
}

/**
 * Determine whether the clearance publish task should be visible.
 */
function setup_task_can_view(): bool {
	try {
		return clearance_page_exists();
	} catch ( \Throwable $e ) {
		return false;
	}
}

/**
 * Determine whether the clearance publish task is complete.
 */
function setup_task_is_complete(): bool {
	try {
		return clearance_page_is_published();
	} catch ( \Throwable $e ) {
		return false;
	}
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
