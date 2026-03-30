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
 * @since 1.0.0
 */
const ACTIVATED_AT_OPTION = 'wc_clearance_activated_at';

/**
 * Seed the activation timestamp option with the current time.
 *
 * Does nothing if the option already exists.
 *
 * @since 1.0.0
 */
function seed_activated_at_option(): void {
	add_option( ACTIVATED_AT_OPTION, time() );
}
