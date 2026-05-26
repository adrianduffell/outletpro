<?php
/**
 * Block pattern functions.
 *
 * @package WC_Outlet
 */

namespace WC_Outlet;

defined( 'ABSPATH' ) || exit;

/**
 * Register the outlet block pattern category.
 *
 * @internal
 */
function register_outlet_block_pattern_category(): void {
	register_block_pattern_category(
		'wc-outlet',
		array(
			'label' => __( 'Outlet', 'wc-outlet' ),
		)
	);
}
