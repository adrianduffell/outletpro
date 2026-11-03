<?php
/**
 * Database migrations.
 *
 * Migrations are run each time config items are added or removed. This includes
 * settings, theme mods, and taxonomies. All migrations should be backwards
 * compatible to allow the plugin to be rolled back to an earlier version.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

if ( version_compare( get_option( 'outletpro_db_version', '0' ), DB_VERSION, '>=' ) ) {
	return;
}

// No migrations yet.

update_option( 'outletpro_db_version', DB_VERSION, true );
