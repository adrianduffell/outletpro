<?php
/**
 * Theme JSON integration functions.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Initialize theme JSON integrations.
 *
 * @internal
 */
function init_theme_json(): void {
	add_filter( 'wp_theme_json_data_blocks', 'OutletPro\append_badge_color_presets_hook' );
}

/**
 * De-initialize theme JSON integrations.
 *
 * @internal
 */
function deinit_theme_json(): void {
	remove_filter( 'wp_theme_json_data_blocks', 'OutletPro\append_badge_color_presets_hook' );
}

/**
 * Append badge colors to the outlet tile palette.
 *
 * Fired by `wp_theme_json_data_blocks`.
 *
 * @internal WordPress filter hook
 *
 * @param \WP_Theme_JSON_Data $theme_json Theme JSON data.
 * @return \WP_Theme_JSON_Data Modified theme JSON data.
 */
function append_badge_color_presets_hook( \WP_Theme_JSON_Data $theme_json ): \WP_Theme_JSON_Data {
	$settings = \WP_Theme_JSON_Resolver::get_theme_data()->get_settings();
	$palette  = array_column(
		$settings['blocks']['outletpro/outlet-tile']['color']['palette']['theme']
			?? $settings['color']['palette']['theme']
			?? array(),
		null,
		'slug'
	);

	$bg_color   = get_option( OUTLET_BADGE_BG_COLOR_OPTION, '' );
	$text_color = get_option( OUTLET_BADGE_TEXT_COLOR_OPTION, '' );

	$palette['outletpro-badge-bg-color']   = array(
		'slug'  => 'outletpro-badge-bg-color',
		'name'  => __( 'Outlet badge background color', 'outletpro' ),
		'color' => empty( $bg_color ) ? '#000000' : $bg_color,
	);
	$palette['outletpro-badge-text-color'] = array(
		'slug'  => 'outletpro-badge-text-color',
		'name'  => __( 'Outlet badge text color', 'outletpro' ),
		'color' => empty( $text_color ) ? '#FFFFFF' : $text_color,
	);

	return $theme_json->update_with(
		array(
			'version'  => 3,
			'settings' => array(
				'blocks' => array(
					'outletpro/outlet-tile' => array(
						'color' => array( 'palette' => array( 'theme' => array_values( $palette ) ) ),
					),
				),
			),
		)
	);
}
