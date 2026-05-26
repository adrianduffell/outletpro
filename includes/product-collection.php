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
	add_filter( 'query_loop_block_query_vars', 'WC_Outlet\filter_outlet_product_collection_hook', 11, 3 );
}

/**
 * Filter the query vars for the outlet product collection block.
 *
 * Restricts the product collection query to only return products that have
 * the outlet canonical term assigned.
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
function filter_outlet_product_collection_hook( array $query, \WP_Block $block, int $page ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	$is_outlet_query = $block->context['query']['wc_outlet'] ?? false;

	if ( ! $is_outlet_query ) {
		return $query;
	}

	$canonical_term = get_term_by( 'name', OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY );
	if ( ! $canonical_term ) {
		$canonical_term = 0;
	}

	if ( ! isset( $query['tax_query'] ) || ! is_array( $query['tax_query'] ) ) {
		$query['tax_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	}

	$query['tax_query'][] = array(
		'taxonomy' => OUTLET_STATUS_TAXONOMY,
		'field'    => 'term_id',
		'terms'    => $canonical_term instanceof \WP_Term ? $canonical_term->term_id : 0,
	);

	return $query;
}
