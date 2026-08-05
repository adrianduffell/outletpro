<?php
/**
 * License settings functions.
 *
 * @package OutletPro
 * @subpackage License
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * WordPress option group used to store the license key.
 *
 * @internal
 */
const LICENSE_OPTIONS_GROUP = 'outletpro_license';

/**
 * WordPress option key used to store the license key.
 *
 * @internal
 */
const LICENSE_KEY_OPTION = 'outletpro_license_key';

/**
 * WordPress transient key used to cache license validity.
 *
 * @internal
 */
const HAS_LICENSE_TRANSIENT = 'outletpro_has_license';

/**
 * Helper to initialize license settings.
 *
 * @internal
 */
function init_license_settings(): void {
	register_license_key_setting();

	add_action( 'add_option_' . LICENSE_KEY_OPTION, 'OutletPro\invalidate_license_cache_hook', 10, 0 );
	add_action( 'update_option_' . LICENSE_KEY_OPTION, 'OutletPro\invalidate_license_cache_hook', 10, 0 );
	add_action( 'delete_option_' . LICENSE_KEY_OPTION, 'OutletPro\invalidate_license_cache_hook', 10, 0 );
}

/**
 * Helper to deinitialize license settings.
 *
 * @internal
 */
function deinit_license_settings(): void {
	unregister_setting( LICENSE_OPTIONS_GROUP, LICENSE_KEY_OPTION );
	remove_action( 'add_option_' . LICENSE_KEY_OPTION, 'OutletPro\invalidate_license_cache_hook', 10, 0 );
	remove_action( 'update_option_' . LICENSE_KEY_OPTION, 'OutletPro\invalidate_license_cache_hook', 10, 0 );
	remove_action( 'delete_option_' . LICENSE_KEY_OPTION, 'OutletPro\invalidate_license_cache_hook', 10, 0 );
}


/**
 * Register the license key setting.
 *
 * @internal
 */
function register_license_key_setting(): void {
	register_setting(
		LICENSE_OPTIONS_GROUP,
		LICENSE_KEY_OPTION,
		array(
			'type'              => 'string',
			'label'             => __( 'License Key', 'outletpro' ),
			'description'       => __( 'Outlet Pro license key.', 'outletpro' ),
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => array(
				'schema' => array(
					'type' => 'string',
				),
			),
		)
	);
}

/**
 * Invalidate the license cache when the license key option is added, updated, or deleted.
 *
 * Fired by `add_option_{LICENSE_KEY_OPTION}`, `update_option_{LICENSE_KEY_OPTION}`, and `delete_option_{LICENSE_KEY_OPTION}`.
 *
 * @internal WordPress action hook
 */
function invalidate_license_cache_hook(): void {
	delete_transient( HAS_LICENSE_TRANSIENT );
}
