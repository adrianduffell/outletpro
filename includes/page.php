<?php
/**
 * Page functions.
 *
 * @package WC_Outlet
 */

namespace WC_Outlet;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize page integrations.
 *
 * @internal
 */
function init_page(): void {
	register_outlet_page_template();
}

/**
 * Register the outlet page template.
 *
 * @internal
 */
function register_outlet_page_template(): void {
	$template_path = dirname( PLUGIN_FILE ) . '/templates/outlet-page.html';

	if ( ! is_readable( $template_path ) ) {
		return;
	}

	$template_content = file_get_contents( $template_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Loading local template file from plugin directory.

	if ( false === $template_content ) {
		return;
	}

	register_block_template(
		'wc-outlet//outlet-page',
		array(
			'title'       => __( 'Outlet page', 'wc-outlet' ),
			'description' => __( 'Wide page template for the outlet page.', 'wc-outlet' ),
			'post_types'  => array( 'page' ),
			'content'     => $template_content,
			'plugin'      => 'wc-outlet',
		)
	);
}

/**
 * Check if the outlet page exists.
 *
 * This performs heuristics on the {@see OUTLET_PAGE_OPTION} option value.
 *
 * It is considered to exist when the option exists and contains the page ID
 * of a WordPress page.
 *
 * If the option is missing, the outlet page is considered not registered
 * and the function returns false.
 *
 * Zero and non-digit values indicate a corrupted state and the page existence cannot
 * be determined. Exceptions are thrown in these cases.
 *
 * Trashed pages are ignored.
 *
 * @since 1.0.0
 * @throws \UnexpectedValueException If the stored option value is not an integer greater than zero.
 */
function outlet_page_exists(): bool {
	$page_id = get_option( OUTLET_PAGE_OPTION, false );

	// The option does not exist, therefore the page does not exist.
	if ( false === $page_id ) {
		return false;
	}

	// Non-digit values are invalid and indicate a misconfiguration.
	if ( ! ctype_digit( (string) $page_id ) ) {
		throw new \UnexpectedValueException( 'Outlet page option is not a positive integer.' );
	}

	// At this point the value can only be an integer >= 0.
	// Cast to int because caching layers may have returned it as a string.
	$page_id = (int) $page_id;

	// Zero indicates a corrupted state.
	if ( 0 === $page_id ) {
		throw new \UnexpectedValueException( 'Outlet page option value is zero.' );
	}

	$page = get_post( $page_id );

	// Validate post type and status.
	return $page instanceof \WP_Post
	&& 'page' === $page->post_type
	&& 'trash' !== $page->post_status;
}

/**
 * Helper to report diagnostic info on the outlet page.
 *
 * @internal
 * @return array<string, array{0: string, 1: string}>
 */
function report_page(): array {
	$label = __( 'Page ID', 'wc-outlet' );
	try {
		$page_id = get_outlet_page_id();
	} catch ( \UnexpectedValueException $e ) {
		return array( 'outlet-page-id' => array( $label, __( 'Not found', 'wc-outlet' ) ) );
	}
	$page_id = get_outlet_page_id();
	$page    = $page_id ? get_post( $page_id ) : null;

	if ( ! $page instanceof \WP_Post || 'page' !== $page->post_type ) {
		return array( 'outlet-page-id' => array( $label, __( 'Not found', 'wc-outlet' ) ) );
	}

	$status_object = get_post_status_object( $page->post_status );
	$status_label  = $status_object ? $status_object->label : $page->post_status;

	return array( 'outlet-page-id' => array( $label, sprintf( '%d (%s)', $page_id, $status_label ) ) );
}

/**
 * Create the outlet page.
 *
 * Does nothing if a outlet page is already registered via the
 * {@see OUTLET_PAGE_OPTION} option, preventing duplicates. If
 * the option value is corrupted, an exception is thrown as page
 * creation cannot be safely performed.
 *
 * @since 1.0.0
 * @throws \RuntimeException If it cannot be determined whether the outlet page exists.
 * @throws \RuntimeException If the page could not be created.
 */
function create_outlet_page(): void {
	// Prevent duplicate pages from being created.
	try {
		if ( outlet_page_exists() ) {
			return;
		}
	} catch ( \UnexpectedValueException $e ) {
		throw new \RuntimeException(
			'Could not determine whether the outlet page exists.',
			0,
			$e
		);
	}

	if ( wp_is_block_theme() ) {
		$block_attrs = wp_json_encode(
			array(
				'queryId'       => 1,
				'query'         => array(
					'perPage'                       => 9,
					'pages'                         => 0,
					'offset'                        => 0,
					'postType'                      => 'product',
					'order'                         => 'asc',
					'orderBy'                       => 'title',
					'search'                        => '',
					'exclude'                       => array(),
					'inherit'                       => false,
					'isProductCollectionBlock'      => true,
					'wc_outlet'                     => true,
					'featured'                      => false,
					'woocommerceOnSale'             => false,
					'woocommerceStockStatus'        => array( 'instock', 'outofstock', 'onbackorder' ),
					'woocommerceAttributes'         => array(),
					'woocommerceHandPickedProducts' => array(),
					'filterable'                    => true,
					'relatedBy'                     => array(
						'categories' => true,
						'tags'       => true,
					),
				),
				'tagName'       => 'div',
				'displayLayout' => array(
					'type'          => 'flex',
					'columns'       => 3,
					'shrinkColumns' => true,
				),
				'dimensions'    => array(
					'widthType' => 'fill',
				),
				'hideControls'  => array( 'inherit' ),
			)
		);
		// phpcs:disable Generic.Strings.UnnecessaryStringConcat.Found
		$post_content = '<!-- wp:woocommerce/product-collection ' . $block_attrs . ' -->' . "\n" .
			'<div class="wp-block-woocommerce-product-collection"><!-- wp:woocommerce/product-template -->' . "\n" .
			'<!-- wp:woocommerce/product-image {"showSaleBadge":false,"imageSizing":"thumbnail","isDescendentOfQueryLoop":true} -->' . "\n" .
			'<!-- wp:woocommerce/product-sale-badge {"align":"right"} /-->' . "\n" .
			'<!-- /wp:woocommerce/product-image -->' . "\n\n" .
			'<!-- wp:post-title {"textAlign":"center","isLink":true,"style":{"spacing":{"margin":{"bottom":"0.75rem","top":"0"}},"typography":{"lineHeight":"1.4"}},"fontSize":"medium","__woocommerceNamespace":"woocommerce/product-collection/product-title"} /-->' . "\n\n" .
			'<!-- wp:woocommerce/product-price {"isDescendentOfQueryLoop":true,"textAlign":"center","fontSize":"small"} /-->' . "\n\n" .
			'<!-- wp:woocommerce/product-button {"textAlign":"center","isDescendentOfQueryLoop":true,"fontSize":"small"} /-->' . "\n" .
			'<!-- /wp:woocommerce/product-template -->' . "\n\n" .
			'<!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"center"}} -->' . "\n" .
			'<!-- wp:query-pagination-previous /-->' . "\n\n" .
			'<!-- wp:query-pagination-numbers /-->' . "\n\n" .
			'<!-- wp:query-pagination-next /-->' . "\n" .
			'<!-- /wp:query-pagination -->' . "\n\n" .
			'<!-- wp:woocommerce/product-collection-no-results -->' . "\n" .
			'<!-- wp:group {"layout":{"type":"flex","orientation":"vertical","justifyContent":"center","flexWrap":"wrap"}} -->' . "\n" .
			'<div class="wp-block-group"><!-- wp:paragraph {"fontSize":"medium"} -->' . "\n" .
			'<p class="has-medium-font-size"><strong>No results found</strong></p>' . "\n" .
			'<!-- /wp:paragraph -->' . "\n\n" .
			'<!-- wp:paragraph -->' . "\n" .
			'<p>You can try <a href="#" class="wc-link-clear-any-filters">clearing any filters</a> or head to our <a href="#" class="wc-link-stores-home">store&#8217;s home</a></p>' . "\n" .
			'<!-- /wp:paragraph --></div>' . "\n" .
			'<!-- /wp:group -->' . "\n" .
			'<!-- /wp:woocommerce/product-collection-no-results --></div>' . "\n" .
			'<!-- /wp:woocommerce/product-collection -->';
		// phpcs:enable
	} else {
		$post_content = '<!-- wp:shortcode -->' . "\n" .
			'[products wc_outlet="yes"]' . "\n" .
			'<!-- /wp:shortcode -->';
	}

	$page_id = wp_insert_post(
		array(
			'post_title'  => __( 'Outlet', 'wc-outlet' ),
			'post_name'   => 'outlet',
			'post_status' => 'draft',
			'post_type'   => 'page',
		),
		true
	);

	if ( is_wp_error( $page_id ) ) {
		throw new \RuntimeException( $page_id->get_error_message() );
	}

	update_option( OUTLET_PAGE_OPTION, $page_id );

	$result = wp_update_post(
		array(
			'ID'           => $page_id,
			'post_content' => $post_content,
		),
		true
	);

	if ( is_wp_error( $result ) ) {
		throw new \RuntimeException( $result->get_error_message() );
	}
}

/**
 * Check whether the outlet page exists and is published.
 *
 * @since 1.0.0
 * @throws \RuntimeException If it cannot be determined whether the outlet page already exists.
 */
function outlet_page_is_published(): bool {
	try {
		if ( ! outlet_page_exists() ) {
			return false;
		}
	} catch ( \UnexpectedValueException $e ) {
		throw new \RuntimeException(
			'Could not determine whether the outlet page already exists.',
			0,
			$e
		);
	}

	$page_id = get_option( OUTLET_PAGE_OPTION );
	$page    = get_post( $page_id );

	return $page instanceof \WP_Post
		&& 'publish' === $page->post_status;
}
