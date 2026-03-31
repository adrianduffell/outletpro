<?php
/**
 * Activation functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * WordPress option key used to store the plugin activation timestamp.
 *
 * @internal
 */
const ACTIVATED_AT_OPTION = 'wc_clearance_activated_at';

/**
 * Seed the activation timestamp option with the current time.
 *
 * Always updates the option, so re-activating the plugin resets the timestamp.
 *
 * @internal
 */
function seed_activated_at_option(): void {
	update_option( ACTIVATED_AT_OPTION, time() );
}
