<?php
/**
 * Content for the price tiers with circles pattern.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_price_tiers;
use const OutletPro\OUTLET_BADGE_BG_COLOR_OPTION;
use const OutletPro\OUTLET_BADGE_TEXT_COLOR_OPTION;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

defined( 'ABSPATH' ) || exit;

$column_count = 4;

try {
	$prices = get_price_tiers();
} catch ( RuntimeException $e ) {
	wc_get_logger()->error( 'Outlet price tiers could not be retrieved.' );
	return;
}

$terms = array();
foreach ( array_slice( $prices, 0, $column_count ) as $price ) {
	$price_term = get_term_by( 'slug', 'outlet-price-' . $price, OUTLET_SEARCH_INDEX_TAXONOMY );
	if ( ! $price_term instanceof WP_Term ) {
		continue;
	}
	$terms[] = $price_term;
}

$bg_color   = get_option( OUTLET_BADGE_BG_COLOR_OPTION, null );
$text_color = get_option( OUTLET_BADGE_TEXT_COLOR_OPTION, null );

$tiles = array();

// Pad with blank tiles when there is not enough content.
foreach ( array_pad( $terms, $column_count, null ) as $tile_term ) {
	$tile_attrs = array(
		'category'                => $tile_term->term_id ?? 0,
		'displayOutletBadgeLabel' => false,
		'style'                   => array(
			'border'     => array(
				'style'  => 'none',
				'width'  => '0',
				'radius' => '50%',
			),
			'dimensions' => array(
				'aspectRatio' => '1',
			),
			'color'      => array( 'background' => $bg_color ),
			'spacing'    => array(
				'blockGap' => '0',
				'padding'  => array(
					'top'    => '0.75em',
					'right'  => '0.75em',
					'bottom' => '0.75em',
					'left'   => '0.75em',
				),
			),
			'typography' => array( 'textAlign' => 'center' ),
			'layout'     => array(
				'selfStretch' => 'fill',
			),
			'@tablet'    => array(
				'dimensions' => array(
					'aspectRatio' => '1',
				),
			),
			'@mobile'    => array(
				'dimensions' => array(
					'aspectRatio' => '1',
				),
			),
		),
		'layout'                  => array(
			'type'              => 'flex',
			'orientation'       => 'vertical',
			'justifyContent'    => 'center',
			'verticalAlignment' => 'center',
		),
	);

	$tiles[] = '<!-- wp:outletpro/outlet-tile ' . wp_json_encode( $tile_attrs, JSON_UNESCAPED_SLASHES ) . ' /-->';
}

$grid_attrs = array(
	'align'  => 'full',
	'style'  => array(
		'color'      => array( 'text' => $text_color ),
		'spacing'    => array(
			'blockGap' => 'var:preset|spacing|70',
			'margin'   => array(
				'top'    => 'var:preset|spacing|40',
				'bottom' => 'var:preset|spacing|60',
				'left'   => 'var:preset|spacing|40',
				'right'  => 'var:preset|spacing|40',
			),
		),
		'typography' => array(
			'fontStyle'      => 'normal',
			'fontSize'       => 'clamp(12px, 1.6vw, 45px)',
			'fontWeight'     => '700',
			'lineHeight'     => '1.2',
			'textAlign'      => 'left',
			'textDecoration' => 'none',
		),
		'@tablet'    => array(
			'spacing' => array(
				'blockGap' => 'var:preset|spacing|30',
				'margin'   => array(
					'left'  => '0',
					'right' => '0',
				),
			),
		),
		'@mobile'    => array(
			'spacing' => array(
				'blockGap' => 'var:preset|spacing|30',
				'margin'   => array(
					'left'  => '0',
					'right' => '0',
				),
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
	'showAll' => true,
	'items'   => $menu_categories,
);
foreach ( $facets as $facet ) {
	if ( ! preg_match( '/^outlet-discount-[0-9]+$/D', $facet->slug ) ) {
		continue;
	}

	$menu_attrs['items'][] = $facet->term_id;
}

$menu_content = '<!-- wp:outletpro/outlet-menu ' . wp_json_encode( $menu_attrs, JSON_UNESCAPED_SLASHES ) . ' /-->';
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Block attributes and inline styles are escaped above.
echo '<!-- wp:group {"layout":{"type":"default"}} --><div class="wp-block-group">' .
	'<!-- wp:outletpro/outlet-tile-grid ' . wp_json_encode( $grid_attrs, JSON_UNESCAPED_SLASHES ) . ' -->' . "\n" .
	'<div class="' . esc_attr( trim( 'wp-block-outletpro-outlet-tile-grid alignfull ' . ( $grid_styles['classnames'] ?? '' ) ) ) . '" style="' . esc_attr( $grid_styles['css'] ) . '">' . implode( "\n\n", $tiles ) . '</div>' . "\n" .
	'<!-- /wp:outletpro/outlet-tile-grid -->' .
	$menu_content . '</div><!-- /wp:group -->';
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
