<?php
/**
 * Product collection block functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize the product collection block integration.
 *
 * @internal
 */
function init_product_collection(): void {
	add_filter( 'render_block_data', 'WC_Clearance\inject_clearance_query_flag_hook', 10, 3 );
	add_filter( 'query_loop_block_query_vars', 'WC_Clearance\filter_clearance_product_collection_hook', 11, 3 );
	add_filter( 'rest_product_query', 'WC_Clearance\product_collection_editor_query_hook', 10, 2 );
}

/**
 * Inject the clearance query flag into the product collection block attributes.
 *
 * Ensures descendant pagination and total blocks inherit the clearance marker
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
function inject_clearance_query_flag_hook( array $parsed_block, array $source_block, ?\WP_Block $parent_block ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	if ( 'woocommerce/product-collection' !== ( $parsed_block['blockName'] ?? '' ) ) {
		return $parsed_block;
	}

	$collection = $parsed_block['attrs']['collection'] ?? '';

	if ( 'wc-clearance/product-collection/clearance' !== $collection ) {
		return $parsed_block;
	}

	if ( ! isset( $parsed_block['attrs']['query'] ) || ! is_array( $parsed_block['attrs']['query'] ) ) {
		$parsed_block['attrs']['query'] = array();
	}

	$parsed_block['attrs']['query']['wc_clearance'] = true;

	return $parsed_block;
}

/**
 * Filter the query vars for the clearance product collection block.
 *
 * Restricts the product collection query to only return products that have
 * the clearance canonical term assigned.
 *
 * Fired by `query_loop_block_query_vars`.
 *
 * @internal WordPress filter hook
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
 * @param array<string, mixed> $query   The query vars.
 * @param \WP_Block            $block   The block instance.
 * @param int                  $page    The current page.
 * @return array<string, mixed> Filtered query vars.
 */
function filter_clearance_product_collection_hook( array $query, \WP_Block $block, int $page ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	$is_clearance_query = $block->context['query']['wc_clearance'] ?? false;

	if ( ! $is_clearance_query ) {
		return $query;
	}

	$canonical_term = get_term_by( 'name', CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );
	if ( ! $canonical_term ) {
		$canonical_term = 0;
	}

	if ( ! isset( $query['tax_query'] ) || ! is_array( $query['tax_query'] ) ) {
		$query['tax_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	}

	$query['tax_query'][] = array(
		'taxonomy' => CLEARANCE_STATUS_TAXONOMY,
		'field'    => 'term_id',
		'terms'    => $canonical_term instanceof \WP_Term ? $canonical_term->term_id : 0,
	);

	return $query;
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

	if ( 'wc-clearance/product-collection/clearance' !== $collection ) {
		return $args;
	}

	if ( ! isset( $args['tax_query'] ) || ! is_array( $args['tax_query'] ) ) {
		$args['tax_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	}

	$args['tax_query'][] = array(
		'taxonomy' => CLEARANCE_STATUS_TAXONOMY,
		'field'    => 'slug',
		'terms'    => array( CLEARANCE_STATUS_CANONICAL_TERM ),
	);

	return $args;
}
