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
	add_filter( 'query_loop_block_query_vars', 'WC_Clearance\filter_clearance_product_collection_hook', 11, 3 );
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
	$is_product_collection_block = $block->context['query']['isProductCollectionBlock'] ?? false;

	if ( ! $is_product_collection_block ) {
		return $query;
	}

	$collection = $block->context['collection'] ?? '';
	if ( 'wc-clearance/product-collection/clearance' !== $collection ) {
		return $query;
	}

	$canonical_term = get_term_by( 'name', CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );
	if ( ! $canonical_term ) {
		$canonical_term = 0;
	}

	$query['tax_query']   = $query['tax_query'] ?? array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	$query['tax_query'][] = array(
		'taxonomy' => CLEARANCE_STATUS_TAXONOMY,
		'field'    => 'term_id',
		'terms'    => $canonical_term instanceof \WP_Term ? $canonical_term->term_id : $canonical_term,
	);

	return $query;
}
