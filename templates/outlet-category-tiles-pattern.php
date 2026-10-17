<?php
/**
 * Category selection for outlet tile patterns.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 *
 * @var int $column_count Number of category tiles.
 */

defined( 'ABSPATH' ) || exit;

$terms = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'parent'     => 0,
		'hide_empty' => false,
		'orderby'    => 'count',
		'order'      => 'DESC',
		'number'     => $column_count,
	)
);

if ( is_wp_error( $terms ) ) {
	wc_get_logger()->error( 'Product categories could not be retrieved.' );
	return '';
}

// Re-sort the categories by menu order.
if ( ! empty( $terms ) ) {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'include'    => wp_list_pluck( $terms, 'term_id' ),
			'orderby'    => 'menu_order',
			'order'      => 'ASC',
		)
	);

	if ( is_wp_error( $terms ) ) {
		wc_get_logger()->error( 'Product categories could not be retrieved.' );
		return '';
	}
}

return require __DIR__ . '/outlet-tiles-pattern.php';
