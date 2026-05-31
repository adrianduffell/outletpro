<?php
/**
 * Block pattern registration.
 *
 * @package WC_Outlet
 */

namespace WC_Outlet;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize block patterns.
 *
 * @internal
 */
function init_patterns(): void {
	register_outlet_block_pattern_category();
	register_outlet_filter_tiles_pattern();

	if ( version_compare( get_bloginfo( 'version' ), '7.0', '<' ) ) {
		return;
	}

	register_outlet_sort_filter_pattern();
}

/**
 * Helper to de-initialize block patterns back to the uninitialized state.
 *
 * @internal
 */
function deinit_patterns(): void {
	$patterns = array_filter(
		\WP_Block_Patterns_Registry::get_instance()->get_all_registered(),
		fn( $pattern ) => 0 === strpos( $pattern['name'], 'wc-outlet/' )
	);

	foreach ( $patterns as $pattern ) {
		unregister_block_pattern( $pattern['name'] );
	}

	$categories = array_filter(
		\WP_Block_Pattern_Categories_Registry::get_instance()->get_all_registered(),
		fn( $category ) => 0 === strpos( $category['name'], 'wc-outlet' )
	);

	foreach ( $categories as $category ) {
		unregister_block_pattern_category( $category['name'] );
	}
}

/**
 * Get block content for a registered pattern, including editor metadata.
 *
 * @param string $pattern_name Pattern slug.
 *
 * @throws \InvalidArgumentException If the pattern name is empty or has unsupported characters.
 * @throws \InvalidArgumentException If the pattern is not registered.
 * @throws \RuntimeException If block parsing fails.
 * @throws \RuntimeException If pattern resolution fails.
 * @throws \RuntimeException If block serialization fails.
 */
function get_pattern_content( string $pattern_name ): string {
	if ( '' === $pattern_name ) {
		throw new \InvalidArgumentException( 'Pattern name cannot be empty.' );
	}

	if ( ! preg_match( '/^[a-z0-9\/-]+$/', $pattern_name ) ) {
		throw new \InvalidArgumentException( 'Pattern name contains unsupported characters.' );
	}

	if ( ! \WP_Block_Patterns_Registry::get_instance()->is_registered( $pattern_name ) ) {
		throw new \InvalidArgumentException(
			sprintf( 'Block pattern "%s" is not registered.', $pattern_name )
		);
	}

	$parsed_blocks = parse_blocks(
		sprintf(
			'<!-- wp:pattern %s /-->',
			wp_json_encode(
				array(
					'slug' => $pattern_name,
				),
				JSON_INVALID_UTF8_SUBSTITUTE
			)
		)
	);

	if ( empty( $parsed_blocks ) ) {
		throw new \RuntimeException( 'Failed to parse block pattern markup.' );
	}

	$resolved_blocks = resolve_pattern_blocks( $parsed_blocks );

	if ( empty( $resolved_blocks ) ) {
		throw new \RuntimeException( 'Failed to resolve block pattern markup.' );
	}

	$content = serialize_blocks( $resolved_blocks );

	if ( '' === $content ) {
		throw new \RuntimeException( 'Failed to serialize resolved block pattern markup.' );
	}

	return $content;
}

/**
 * Get the block markup content for the outlet filter tiles pattern.
 *
 * @internal
 * @param bool $include_metadata Whether to include pattern metadata on the wp:buttons block. Default false.
 * @return string Block markup string.
 */
function get_outlet_filter_tiles_content( bool $include_metadata = false ): string {
	$currency  = get_woocommerce_currency();
	$tiers_map = array(
		'AED' => array( 25, 50, 100 ),
		'AFN' => array( 500, 1000, 2500 ),
		'ALL' => array( 1000, 2500, 5000 ),
		'AMD' => array( 5000, 10000, 25000 ),
		'ANG' => array( 25, 50, 100 ),
		'AOA' => array( 10000, 25000, 50000 ),
		'ARS' => array( 10000, 25000, 50000 ),
		'AUD' => array( 15, 30, 50 ),
		'AWG' => array( 25, 50, 100 ),
		'AZN' => array( 25, 50, 100 ),
		'BAM' => array( 25, 50, 100 ),
		'BBD' => array( 25, 50, 100 ),
		'BDT' => array( 1000, 2500, 5000 ),
		'BGN' => array( 25, 50, 100 ),
		'BHD' => array( 10, 25, 50 ),
		'BIF' => array( 10000, 25000, 50000 ),
		'BMD' => array( 10, 25, 50 ),
		'BND' => array( 15, 30, 50 ),
		'BOB' => array( 100, 250, 500 ),
		'BRL' => array( 50, 100, 200 ),
		'BSD' => array( 10, 25, 50 ),
		'BTN' => array( 500, 1000, 2500 ),
		'BWP' => array( 250, 500, 1000 ),
		'BYN' => array( 25, 50, 100 ),
		'BZD' => array( 25, 50, 100 ),
		'CAD' => array( 15, 30, 50 ),
		'CDF' => array( 10000, 25000, 50000 ),
		'CHF' => array( 10, 20, 40 ),
		'CLP' => array( 10000, 25000, 50000 ),
		'CNY' => array( 100, 250, 500 ),
		'COP' => array( 100000, 250000, 500000 ),
		'CRC' => array( 10000, 25000, 50000 ),
		'CUP' => array( 250, 500, 1000 ),
		'CVE' => array( 1000, 2500, 5000 ),
		'CZK' => array( 250, 500, 1000 ),
		'DJF' => array( 1000, 3000, 5000 ),
		'DKK' => array( 100, 250, 500 ),
		'DOP' => array( 1000, 2500, 5000 ),
		'DZD' => array( 1000, 2500, 5000 ),
		'EGP' => array( 500, 1000, 2500 ),
		'ERN' => array( 250, 500, 1000 ),
		'ETB' => array( 1000, 2500, 5000 ),
		'EUR' => array( 10, 25, 50 ),
		'FJD' => array( 25, 50, 100 ),
		'FKP' => array( 10, 20, 40 ),
		'GBP' => array( 10, 20, 40 ),
		'GEL' => array( 25, 50, 100 ),
		'GHS' => array( 250, 500, 1000 ),
		'GIP' => array( 10, 20, 40 ),
		'GMD' => array( 1000, 2500, 5000 ),
		'GNF' => array( 100000, 250000, 500000 ),
		'GTQ' => array( 100, 250, 500 ),
		'GYD' => array( 5000, 10000, 25000 ),
		'HKD' => array( 100, 250, 500 ),
		'HNL' => array( 500, 1000, 2500 ),
		'HRK' => array( 100, 250, 500 ),
		'HTG' => array( 1000, 2500, 5000 ),
		'HUF' => array( 5000, 10000, 25000 ),
		'IDR' => array( 100000, 250000, 500000 ),
		'ILS' => array( 50, 100, 200 ),
		'INR' => array( 500, 1000, 2500 ),
		'IQD' => array( 10000, 25000, 50000 ),
		'IRR' => array( 1000000, 2500000, 5000000 ),
		'ISK' => array( 1000, 3000, 5000 ),
		'JMD' => array( 5000, 10000, 25000 ),
		'JOD' => array( 10, 25, 50 ),
		'JPY' => array( 1000, 3000, 5000 ),
		'KES' => array( 1000, 2500, 5000 ),
		'KGS' => array( 1000, 2500, 5000 ),
		'KHR' => array( 10000, 25000, 50000 ),
		'KMF' => array( 1000, 3000, 5000 ),
		'KRW' => array( 10000, 30000, 50000 ),
		'KWD' => array( 10, 25, 50 ),
		'KYD' => array( 10, 25, 50 ),
		'KZT' => array( 5000, 10000, 25000 ),
		'LAK' => array( 100000, 250000, 500000 ),
		'LBP' => array( 1000000, 2500000, 5000000 ),
		'LKR' => array( 5000, 10000, 25000 ),
		'LRD' => array( 5000, 10000, 25000 ),
		'LSL' => array( 250, 500, 1000 ),
		'LYD' => array( 25, 50, 100 ),
		'MAD' => array( 100, 250, 500 ),
		'MDL' => array( 250, 500, 1000 ),
		'MGA' => array( 10000, 25000, 50000 ),
		'MKD' => array( 1000, 2500, 5000 ),
		'MMK' => array( 10000, 25000, 50000 ),
		'MNT' => array( 10000, 25000, 50000 ),
		'MOP' => array( 100, 250, 500 ),
		'MRU' => array( 1000, 2500, 5000 ),
		'MUR' => array( 1000, 2500, 5000 ),
		'MVR' => array( 250, 500, 1000 ),
		'MWK' => array( 10000, 25000, 50000 ),
		'MXN' => array( 200, 500, 1000 ),
		'MYR' => array( 50, 100, 200 ),
		'MZN' => array( 1000, 2500, 5000 ),
		'NAD' => array( 250, 500, 1000 ),
		'NGN' => array( 10000, 25000, 50000 ),
		'NIO' => array( 1000, 2500, 5000 ),
		'NOK' => array( 100, 250, 500 ),
		'NPR' => array( 500, 1000, 2500 ),
		'NZD' => array( 15, 30, 50 ),
		'OMR' => array( 10, 25, 50 ),
		'PAB' => array( 10, 25, 50 ),
		'PEN' => array( 50, 100, 200 ),
		'PGK' => array( 50, 100, 200 ),
		'PHP' => array( 1000, 2500, 5000 ),
		'PKR' => array( 10000, 25000, 50000 ),
		'PLN' => array( 50, 100, 200 ),
		'PYG' => array( 100000, 250000, 500000 ),
		'QAR' => array( 50, 100, 200 ),
		'RON' => array( 50, 100, 200 ),
		'RSD' => array( 1000, 2500, 5000 ),
		'RUB' => array( 1000, 2500, 5000 ),
		'RWF' => array( 10000, 25000, 50000 ),
		'SAR' => array( 50, 100, 200 ),
		'SBD' => array( 100, 250, 500 ),
		'SCR' => array( 250, 500, 1000 ),
		'SEK' => array( 100, 250, 500 ),
		'SGD' => array( 15, 30, 50 ),
		'SHP' => array( 10, 20, 40 ),
		'SLE' => array( 250, 500, 1000 ),
		'SLL' => array( 10000, 25000, 50000 ),
		'SOS' => array( 10000, 25000, 50000 ),
		'SRD' => array( 1000, 2500, 5000 ),
		'SSP' => array( 10000, 25000, 50000 ),
		'STN' => array( 250, 500, 1000 ),
		'SVC' => array( 10, 25, 50 ),
		'SYP' => array( 100000, 250000, 500000 ),
		'SZL' => array( 250, 500, 1000 ),
		'THB' => array( 500, 1000, 2500 ),
		'TJS' => array( 250, 500, 1000 ),
		'TMT' => array( 50, 100, 200 ),
		'TND' => array( 50, 100, 200 ),
		'TOP' => array( 25, 50, 100 ),
		'TRY' => array( 500, 1000, 2500 ),
		'TTD' => array( 100, 250, 500 ),
		'TWD' => array( 500, 1000, 2500 ),
		'TZS' => array( 10000, 25000, 50000 ),
		'UAH' => array( 1000, 2500, 5000 ),
		'UGX' => array( 10000, 25000, 50000 ),
		'USD' => array( 10, 25, 50 ),
		'UYU' => array( 1000, 2500, 5000 ),
		'UZS' => array( 10000, 25000, 50000 ),
		'VES' => array( 500, 1000, 2500 ),
		'VND' => array( 100000, 250000, 500000 ),
		'VUV' => array( 1000, 3000, 5000 ),
		'WST' => array( 25, 50, 100 ),
		'XAF' => array( 10000, 25000, 50000 ),
		'XCD' => array( 25, 50, 100 ),
		'XOF' => array( 10000, 25000, 50000 ),
		'XPF' => array( 10000, 25000, 50000 ),
		'YER' => array( 10000, 25000, 50000 ),
		'ZAR' => array( 250, 500, 1000 ),
		'ZMW' => array( 250, 500, 1000 ),
		'ZWL' => array( 10000, 25000, 50000 ),
	);
	$tiers     = $tiers_map[ $currency ] ?? array( 10, 25, 50 );

	try {
		$page_id = get_outlet_page_id();
	} catch ( \Throwable $e ) {
		return '';
	}
	$permalink = $page_id ? get_permalink( $page_id ) : false;
	if ( ! $permalink ) {
		return '';
	}
	$base_url = $permalink;

	/* translators: %s: formatted price amount with currency symbol, e.g. $10 */
	$label_template = __( 'Under %s', 'wc-outlet' );
	$buttons        = array();

	foreach ( $tiers as $price ) {
		$label     = esc_html( sprintf( $label_template, wp_strip_all_tags( wc_price( $price, array( 'decimals' => 0 ) ) ) ) );
		$href      = esc_url( add_query_arg( 'max_price', $price, $base_url ) );
		$buttons[] =
			'<!-- wp:button {"className":"is-style-outline","style":{"border":{"radius":{"topLeft":"0px","topRight":"0px","bottomLeft":"0px","bottomRight":"0px"}},"typography":{"lineHeight":"1.6"},"spacing":{"padding":{"left":"4vw","right":"4vw"}}}} -->' . "\n" .
			'<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="' . $href . '" style="border-top-left-radius:0px;border-top-right-radius:0px;border-bottom-left-radius:0px;border-bottom-right-radius:0px;line-height:1.6;padding-right:4vw;padding-left:4vw;">' . $label . '</a></div>' . "\n" .
			'<!-- /wp:button -->';
	}

	$buttons_attrs = array(
		'style'     => array(
			'spacing' => array(
				'margin' => array(
					'top'    => 'var:preset|spacing|30',
					'bottom' => 'var:preset|spacing|30',
				),
			),
		),
		'layout'    => array(
			'type'           => 'flex',
			'justifyContent' => 'left',
			'flexWrap'       => 'nowrap',
		),
		'className' => 'wc-outlet-filter-tiles',
	);

	if ( $include_metadata ) {
		$buttons_attrs = array_merge(
			array(
				'metadata' => array(
					'categories'  => array( 'wc-outlet' ),
					'patternName' => 'wc-outlet/outlet-filter-tiles',
					'name'        => __( 'Outlet filter tiles', 'wc-outlet' ),
				),
			),
			$buttons_attrs
		);
	}

	return '<!-- wp:buttons ' . wp_json_encode( $buttons_attrs, JSON_UNESCAPED_SLASHES ) . ' -->' . "\n" .
		'<div class="wp-block-buttons wc-outlet-filter-tiles" style="margin-top:var(--wp--preset--spacing--30);margin-bottom:var(--wp--preset--spacing--30)">' . implode( "\n\n", $buttons ) . '</div>' . "\n" .
		'<!-- /wp:buttons -->';
}

/**
 * Register the outlet filter tiles block pattern.
 *
 * @internal
 */
function register_outlet_filter_tiles_pattern(): void {
	register_block_pattern(
		'wc-outlet/outlet-filter-tiles',
		array(
			'title'         => __( 'Outlet filter tiles', 'wc-outlet' ),
			'description'   => __( 'Adds tiled price range filters for the store\'s outlet page.', 'wc-outlet' ),
			'content'       => get_outlet_filter_tiles_content(),
			'categories'    => array( 'wc-outlet' ),
			'viewportWidth' => 320,
		)
	);
}

/**
 * Get the block markup content for the outlet sort filter pattern.
 *
 * @internal
 * @return string Block markup string.
 */
function get_outlet_sort_filter_pattern_content(): string {
	$template_path = dirname( PLUGIN_FILE ) . '/templates/outlet-sort-filter-pattern.php';

	if ( ! is_readable( $template_path ) ) {
		return '';
	}

	ob_start();
	include $template_path;
	$template_content = ob_get_clean();

	if ( false === $template_content ) {
		return '';
	}

	return $template_content;
}

/**
 * Register the outlet sort filter block pattern.
 *
 * @internal
 */
function register_outlet_sort_filter_pattern(): void {
	register_block_pattern(
		'wc-outlet/outlet-sort-filter',
		array(
			'title'         => __( 'Outlet sort filter', 'wc-outlet' ),
			'description'   => __( 'Dropdown sort filter for the outlet page.', 'wc-outlet' ),
			'content'       => get_outlet_sort_filter_pattern_content(),
			'categories'    => array( 'wc-outlet' ),
			'viewportWidth' => 180,
		)
	);
}

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
