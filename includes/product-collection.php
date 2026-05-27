<?php
/**
 * Product collection block functions.
 *
 * @package WC_Outlet
 */

namespace WC_Outlet;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize the product collection block integration.
 *
 * @internal
 */
function init_product_collection(): void {
	add_filter( 'render_block_data', 'WC_Outlet\inject_outlet_query_flag_hook', 10, 3 );
	add_filter( 'rest_product_query', 'WC_Outlet\product_collection_editor_query_hook', 10, 2 );
}

/**
 * Inject the outlet query flag into the product collection block attributes.
 *
 * Ensures descendant pagination and total blocks inherit the outlet marker
 * through the normal query context propagation mechanism.
 *
 * Fired by `render_block_data`.
 *
 * @internal WordPress filter hook
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
 * @param array<string, mixed> $parsed_block The parsed block data.
 * @param array<string, mixed> $source_block The source block data.
 * @param \WP_Block|null       $parent_block The parent block instance.
 * @return array<string, mixed> Filtered parsed block data.
 */
function inject_outlet_query_flag_hook( array $parsed_block, array $source_block, ?\WP_Block $parent_block ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	if ( 'woocommerce/product-collection' !== ( $parsed_block['blockName'] ?? '' ) ) {
		return $parsed_block;
	}

	$collection = $parsed_block['attrs']['collection'] ?? '';

	if ( 'wc-outlet/product-collection/outlet' !== $collection ) {
		return $parsed_block;
	}

	if ( ! isset( $parsed_block['attrs']['query'] ) || ! is_array( $parsed_block['attrs']['query'] ) ) {
		$parsed_block['attrs']['query'] = array();
	}

	$parsed_block['attrs']['query']['wc_outlet'] = true;

	return $parsed_block;
}

/**
 * Filter the REST API query for the product collection block in the editor.
 *
 * Fired by `rest_product_query`.
 *
 * @internal WordPress filter hook
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
 * @param array<string, mixed> $args    The query args.
 * @param \WP_REST_Request     $request The REST request.
 * @return array<string, mixed> Filtered query args.
 */
function product_collection_editor_query_hook( $args, $request ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	if ( ! $request->get_param( 'isProductCollectionBlock' ) ) {
		return $args;
	}

	$context = $request->get_param( 'productCollectionQueryContext' );

	if ( ! is_array( $context ) ) {
		return $args;
	}

	$collection = $context['collection'] ?? '';

	if ( 'wc-outlet/product-collection/outlet' !== $collection ) {
		return $args;
	}

	if ( ! isset( $args['tax_query'] ) || ! is_array( $args['tax_query'] ) ) {
		$args['tax_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	}

	$args['tax_query'][] = array(
		'taxonomy' => OUTLET_STATUS_TAXONOMY,
		'field'    => 'slug',
		'terms'    => array( OUTLET_STATUS_CANONICAL_TERM ),
	);

	return $args;
}
