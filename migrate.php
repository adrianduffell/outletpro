<?php
/**
 * Settings migrations.
 *
 * Migrations are run each time a setting is added or removed. All migrations
 * should be backwards compatible to allow the plugin to be rolled back to an
 * earlier version.
 *
 * Settings version increments each time a setting is added or removed.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

if ( version_compare( get_option( 'outletpro_settings_version', '0' ), SETTINGS_VERSION, '>=' ) ) {
	return;
}

// No migrations yet.

update_option( 'outletpro_settings_version', SETTINGS_VERSION, true );
