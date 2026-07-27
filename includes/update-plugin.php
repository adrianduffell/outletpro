<?php
/**
 * Plugin update functions.
 *
 * @package OutletPro
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize license features.
 *
 * @internal
 */
function init_update_plugin(): void {
	add_filter( 'update_plugins_adrianduffell.store', 'OutletPro\update_plugin_hook', 10, 3 );
}

/**
 * Helper to de-initialize  plugin updates.
 *
 * @internal
 */
function deinit_update_plugin(): void {
	remove_filter( 'update_plugins_adrianduffell.store', 'OutletPro\update_plugin_hook', 10, 3 );
}



/**
 * Checks for an available Outlet Pro update.
 *
 * Fired by `update_plugins_adrianduffell.store`.
 *
 * @param array<string, mixed>|false $update Existing update information.
 * @param array<string, mixed>       $plugin_data Plugin header data.
 * @param string                     $plugin_file Plugin basename.
 * @internal WordPress filter hook
 */
function update_plugin_hook( array|false $update, array $plugin_data, string $plugin_file ): array|false {
	if ( plugin_basename( PLUGIN_FILE ) !== $plugin_file ) {
		return $update;
	}

	if ( ! has_license() ) {
		return false;
	}

	$license_key = get_option( LICENSE_KEY_OPTION );

	$response = wp_remote_get(
		'https://api.adrianduffell.store/outletpro/v1/updates',
		array(
			'timeout' => 5,
			'headers' => array(
				'Authorization' => 'Bearer ' . $license_key,
				'Accept'        => 'application/json',
			),
		)
	);

	if (
		is_wp_error( $response )
		|| 200 !== wp_remote_retrieve_response_code( $response )
	) {
		\wc_get_logger()->error( 'Could not connect to update server.' );
		return $update;
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );

	if (
		! is_array( $data )
		|| ! isset( $data['version'], $data['url'], $data['package'] )
	) {
		\wc_get_logger()->error( 'Invalid response from update server.' );
		return $update;
	}

	return array(
		'name'         => 'Outlet Pro',
		'slug'         => 'outletpro',
		'version'      => $data['version'],
		'url'          => $data['url'],
		'icons'        => $data['icons'] ?? array(),
		'package'      => $data['package'] ?? '',
		'tested'       => $data['tested'] ?? '',
		'requires_php' => $data['requires_php'] ?? '',
	);
}
