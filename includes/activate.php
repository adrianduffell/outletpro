<?php
/**
 * Activation functions.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * WordPress option key used to store the plugin activation timestamp.
 *
 * @internal
 */
const ACTIVATED_AT_OPTION = 'outletpro_activated_at';

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
