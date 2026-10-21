<?php
/**
 * Content for the six category tiles pattern.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use const OutletPro\OUTLET_BADGE_BG_COLOR_OPTION;
use const OutletPro\OUTLET_BADGE_TEXT_COLOR_OPTION;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

defined( 'ABSPATH' ) || exit;

$column_count        = 6;
$tablet_column_count = 3;

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
	return;
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
		return;
	}
}

$bg_color   = get_option( OUTLET_BADGE_BG_COLOR_OPTION, null );
$text_color = get_option( OUTLET_BADGE_TEXT_COLOR_OPTION, null );

$tiles = array();

// Pad with blank tiles when there is not enough content.
foreach ( array_pad( $terms, $column_count, null ) as $tile_term ) {
	$tile_attrs = array(
		'category'                => $tile_term->term_id ?? 0,
		'displayOutletBadgeLabel' => true,
		'style'                   => array(
			'border'     => array(
				'style' => 'none',
				'width' => '0',
			),
			'dimensions' => array(
				'minHeight' => '200px',
			),
			'color'      => array(
				'background' => $bg_color,
				'text'       => $text_color,
			),
			'typography' => array( 'textDecoration' => 'none' ),
			'spacing'    => array(
				'blockGap' => '0',
				'padding'  => array(
					'top'    => '2em',
					'right'  => '0.75em',
					'bottom' => '2em',
					'left'   => '0.75em',
				),
			),
			'layout'     => array(
				'selfStretch' => 'fill',
			),
			'@tablet'    => array(
				'dimensions' => array(
					'minHeight' => '0',
				),
			),
			'@mobile'    => array(
				'dimensions' => array(
					'minHeight' => '0',
				),
				'spacing'    => array(
					'padding' => array(
						'top'    => '0.5em',
						'bottom' => '0.5em',
					),
				),
			),
		),
		'layout'                  => array(
			'type'              => 'flex',
			'orientation'       => 'vertical',
			'justifyContent'    => 'left',
			'verticalAlignment' => 'center',
		),
	);

	$tiles[] = '<!-- wp:outletpro/outlet-tile ' . wp_json_encode( $tile_attrs, JSON_UNESCAPED_SLASHES ) . ' /-->';
}

$grid_attrs = array(
	'style'  => array(
		'spacing'    => array(
			'blockGap' => 'var:preset|spacing|30',
			'margin'   => array(
				'top'    => 'var:preset|spacing|50',
				'bottom' => 'var:preset|spacing|50',
			),
		),
		'typography' => array(
			'fontStyle'      => 'normal',
			'fontSize'       => 3 === $column_count ? 'clamp(12px, 1.8vw, 45px)' : 'clamp(12px, 1.6vw, 45px)',
			'fontWeight'     => '700',
			'lineHeight'     => '1.2',
			'textAlign'      => 'left',
			'textDecoration' => 'none',
		),
		'@tablet'    => array(
			'layout'     => array(
				'columnCount' => $tablet_column_count,
			),
			'typography' => array(
				'fontSize' => 'clamp(12px, 3.5vw, 45px)',
			),
		),
		'@mobile'    => array(
			'layout'     => array(
				'columnCount' => 1,
			),
			'typography' => array(
				'fontSize' => 'clamp(12px, 6vw, 45px)',
			),
		),
	),
	'layout' => array(
		'type'        => 'grid',
		'columnCount' => $column_count,
	),
);

$grid_styles = wp_style_engine_get_styles( $grid_attrs['style'] );

$menu_categories = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'parent'     => 0,
		'hide_empty' => false,
		'orderby'    => 'menu_order',
		'order'      => 'ASC',
		'fields'     => 'ids',
	)
);
if ( is_wp_error( $menu_categories ) ) {
	wc_get_logger()->error( 'Product categories could not be retrieved.' );
	return;
}

$facets = get_terms(
	array(
		'taxonomy'   => OUTLET_SEARCH_INDEX_TAXONOMY,
		'hide_empty' => false,
	)
);
if ( is_wp_error( $facets ) ) {
	wc_get_logger()->error( 'Outlet search index facets could not be retrieved.' );
	return;
}
usort( $facets, static fn( WP_Term $a, WP_Term $b ): int => strnatcmp( $a->slug, $b->slug ) );

$menu_attrs = array(
	'fontSize' => 'small',
	'showAll'  => true,
	'items'    => $menu_categories,
	'style'    => array(
		'spacing'    => array(
			'blockGap' => '1em',
			'margin'   => array(
				'top'    => '2.5rem',
				'bottom' => '2.5rem',
			),
			'padding'  => array(
				'top'    => '0',
				'right'  => '0',
				'bottom' => '0',
				'left'   => '0',
			),
		),
		'typography' => array( 'textDecoration' => 'none' ),
	),
);
foreach ( $facets as $facet ) {
	if ( ! preg_match( '/^outlet-(price|discount)-[0-9]+$/D', $facet->slug ) ) {
		continue;
	}

	$menu_attrs['items'][] = $facet->term_id;
}

$menu_content = '<!-- wp:outletpro/outlet-menu ' . wp_json_encode( $menu_attrs, JSON_UNESCAPED_SLASHES ) . ' /-->';
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Block attributes and inline styles are escaped above.
echo '<!-- wp:group {"style":{"spacing":{"padding":{"top":"0","right":"0","bottom":"0","left":"0"}}},"layout":{"type":"default"}} --><div class="wp-block-group" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0">' .
	'<!-- wp:outletpro/outlet-tiles ' . wp_json_encode( $grid_attrs, JSON_UNESCAPED_SLASHES ) . ' -->' . "\n" .
	'<div class="' . esc_attr( trim( 'wp-block-outletpro-outlet-tiles ' . ( $grid_styles['classnames'] ?? '' ) ) ) . '" style="' . esc_attr( $grid_styles['css'] ) . '">' . implode( "\n\n", $tiles ) . '</div>' . "\n" .
	'<!-- /wp:outletpro/outlet-tiles -->' .
	$menu_content . '</div><!-- /wp:group -->';
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
