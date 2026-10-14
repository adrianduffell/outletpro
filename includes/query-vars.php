<?php
/**
 * Outlet query variable functions.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Initialize public Outlet query variable registration.
 *
 * @internal
 */
function init_query_vars(): void {
	add_filter( 'query_vars', 'OutletPro\register_outlet_query_vars_hook' );
}

/**
 * Remove public Outlet query variable registration.
 *
 * @internal
 */
function deinit_query_vars(): void {
	remove_filter( 'query_vars', 'OutletPro\register_outlet_query_vars_hook' );
}

/**
 * Register public query variables for the Outlet filters.
 *
 * Fired by `query_vars`.
 *
 * @internal WordPress filter hook
 * @param string[] $vars Existing public query variables.
 * @return string[] Public query variables with Outlet filters.
 */
function register_outlet_query_vars_hook( array $vars ): array {
	return array_values( array_unique( array_merge( $vars, array( 'outlet_category', 'outlet_price', 'outlet_discount' ) ) ) );
}

/**
 * Get the requested Outlet product category slug.
 *
 * @internal
 */
function get_category_filter_query_param(): ?string {
	$value = get_query_var( 'outlet_category', '' );

	if ( ! is_string( $value ) ) {
		return null;
	}

	$slug = sanitize_title( $value );
	if ( '' === $slug ) {
		return null;
	}

	return $slug;
}

/**
 * Get the requested Outlet discount search index facet.
 *
 * @internal
 * @return string|null Existing discount facet slug, or null for invalid input.
 */
function get_discount_filter_query_param(): ?string {
	$value = get_query_var( 'outlet_discount', '' );
	if ( ! is_string( $value ) ) {
		return null;
	}
	$discount = filter_var( $value, FILTER_VALIDATE_INT );
	if ( false === $discount ) {
		return null;
	}
	if ( $discount < 1 ) {
		return null;
	}
	if ( $discount > 100 ) {
		return null;
	}
	$slug = 'outlet-discount-' . $discount;
	$term = get_term_by( 'slug', $slug, OUTLET_SEARCH_INDEX_TAXONOMY );
	if ( ! $term instanceof \WP_Term ) {
		return null;
	}
	return $slug;
}

/**
 * Get the requested Outlet price search index facet.
 *
 * @internal
 * @return string|null Existing price facet slug, or null for invalid input.
 */
function get_price_filter_query_param(): ?string {
	$value = get_query_var( 'outlet_price', '' );
	if ( ! is_string( $value ) ) {
		return null;
	}
	$price = filter_var( $value, FILTER_VALIDATE_INT );
	if ( false === $price ) {
		return null;
	}
	if ( $price < 1 ) {
		return null;
	}
	$slug = 'outlet-price-' . $price;
	$term = get_term_by( 'slug', $slug, OUTLET_SEARCH_INDEX_TAXONOMY );
	if ( ! $term instanceof \WP_Term ) {
		return null;
	}
	return $slug;
}
