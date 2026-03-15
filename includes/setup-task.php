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
 * Register the clearance page setup task with WooCommerce's onboarding task list.
 *
 * @since 1.0.0
 * @throws \RuntimeException If the WooCommerce TaskLists class is not available.
 */
function init_setup_task(): void {
	if ( ! class_exists( 'Automattic\WooCommerce\Admin\Features\OnboardingTasks\TaskLists' ) ) {
		throw new \RuntimeException( 'WooCommerce TaskLists class not found. This plugin requires WooCommerce to be active.' );
	}

	// phpcs:disable Squiz.Commenting.FunctionComment.Missing
	TaskLists::add_task(
		'extended',
		new class( TaskLists::get_list( 'extended' ) ) extends Task {
			public function get_id(): string {
				return 'publish-clearance-page';
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
	// phpcs:enable Squiz.Commenting.FunctionComment.Missing
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
	if ( ! current_user_can( 'edit_pages' ) ) {
		return false;
	}

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
