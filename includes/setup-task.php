<?php
/**
 * Setup task functions.
 *
 * @package WC_Outlet
 */

namespace WC_Outlet;

defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Admin\Features\OnboardingTasks\Task;
use Automattic\WooCommerce\Admin\Features\OnboardingTasks\TaskLists;

/**
 * Register the outlet setup task with WooCommerce's onboarding task list.
 *
 * @internal
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
				return 'wc-outlet-include-products';
			}

			public function get_title(): string {
				return __( ' Choose outlet products', 'outletpro' );
			}

			public function get_content(): string {
				return __( 'Move old stock quickly with the store’s outlet.', 'outletpro' );
			}

			public function get_time(): string {
				return '';
			}

			public function get_action_label(): string {
				return __( 'Manage products', 'outletpro' );
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
 * Returns the URL for the outlet setup task action button.
 *
 * @internal
 */
function setup_task_action_url(): string {
	return admin_url( 'edit.php?post_type=product' );
}

/**
 * Determine whether the outlet include products task should be visible.
 *
 * @internal
 */
function setup_task_can_view(): bool {
	// Only show the task to users who can edit products.
	return current_user_can( 'edit_products' );
}

/**
 * Determine whether the outlet include products task is complete.
 *
 * @internal
 */
function setup_task_is_complete(): bool {
	try {
		return ! outlet_empty();
	} catch ( \Throwable $e ) {
		return false;
	}
}
