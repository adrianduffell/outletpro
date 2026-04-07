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
 * Register the clearance section setup task with WooCommerce's onboarding task list.
 *
 * @internal
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
				return 'wc-clearance-include-products';
			}

			public function get_title(): string {
				return __( 'Include products in the clearance section', 'wc-clearance' );
			}

			public function get_content(): string {
				return __( 'Include products in the clearance section to move old stock quickly.', 'wc-clearance' );
			}

			public function get_time(): string {
				return '';
			}

			public function get_action_label(): string {
				return __( 'Manage products', 'wc-clearance' );
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
 * Returns the URL for the clearance section setup task action button.
 */
function setup_task_action_url(): string {
	return admin_url( 'edit.php?post_type=product' );
}

/**
 * Determine whether the clearance include products task should be visible.
 */
function setup_task_can_view(): bool {
	// Only show the task to users who can edit products.
	return current_user_can( 'edit_products' );
}

/**
 * Determine whether the clearance include products task is complete.
 */
function setup_task_is_complete(): bool {
	try {
		return ! clearance_section_empty();
	} catch ( \Throwable $e ) {
		return false;
	}
}
