<?php
/**
 * Publish clearance page task class.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Admin\Features\OnboardingTasks\Task;

/**
 * WooCommerce setup task to publish the clearance section page.
 *
 * Appears in the extended task list (Things to do next) and is
 * marked complete when the clearance section page is published.
 *
 * @since 1.0.0
 */
class Publish_Clearance_Page_Task extends Task {

	/**
	 * Get the task ID.
	 */
	public function get_id(): string {
		return 'publish-clearance-page';
	}

	/**
	 * Get the task title.
	 */
	public function get_title(): string {
		return __( 'Publish the clearance section page', 'wc-clearance' );
	}

	/**
	 * Get the task content.
	 */
	public function get_content(): string {
		return __( 'Publish the clearance section page to make it visible to your customers.', 'wc-clearance' );
	}

	/**
	 * Get the estimated time for the task.
	 */
	public function get_time(): string {
		return __( '1 minute', 'wc-clearance' );
	}
}
