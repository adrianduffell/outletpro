<?php
/**
 * Product functions.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Classify the price tier for the product.
 *
 * @param \WC_Product $product Product to classify.
 * @return int|null Matching price tier threshold.
 * @throws \RuntimeException If the product price is invalid.
 * @throws \RuntimeException If the price tiers cannot be retrieved.
 * @internal
 */
function classify_price_tier( \WC_Product $product ): ?int {
	if ( $product->is_type( 'variable' ) ) {
		return classify_price_tier_for_variable_product( $product );
	}

	$price = $product->get_price();
	// WooCommerce represents unset prices as empty strings.
	if ( '' === $price ) {
		return null;
	}
	if ( ! is_numeric( $price ) ) {
		throw new \RuntimeException( 'Outlet product price is invalid.' );
	}

	$tiers = get_price_tiers();
	$match = null;
	$best  = INF;
	foreach ( $tiers as $threshold ) {
		if ( $price > $threshold ) {
			continue;
		}
		if ( $threshold >= $best ) {
			continue;
		}
		$match = $threshold;
		$best  = $threshold;
	}

	return $match;
}

/**
 * Classify the price tier for the product.
 *
 * Returns the lowest price tier across all visible variations.
 *
 * @param \WC_Product_Variable $product Product to classify.
 * @return int|null Smallest matching variation price tier.
 * @throws \RuntimeException If a variation cannot be retrieved or classified.
 * @internal
 */
function classify_price_tier_for_variable_product( \WC_Product_Variable $product ): ?int {
	// Determine the visible variations from the price lookup.
	$prices        = $product->get_variation_prices( false );
	$variation_ids = array_keys( $prices['price'] );

	$tier = null;
	foreach ( $variation_ids as $variation_id ) {
		$variation = wc_get_product( $variation_id );
		if ( ! $variation ) {
			throw new \RuntimeException( 'Outlet product variation could not be retrieved.' );
		}
		$variation_tier = classify_price_tier( $variation );
		if ( null === $variation_tier ) {
			continue;
		}
		$tier = null === $tier ? $variation_tier : min( $tier, $variation_tier );
	}
	return $tier;
}

/**
 * Classify the discount tier for the product.
 *
 * @param \WC_Product $product Product to classify.
 * @return int|null Matching discount tier threshold.
 * @throws \RuntimeException If the product prices are invalid.
 * @throws \RuntimeException If the discount tiers cannot be retrieved.
 * @internal
 */
function classify_discount_tier( \WC_Product $product ): ?int {
	if ( $product->is_type( 'variable' ) ) {
		return classify_discount_tier_for_variable_product( $product );
	}

	$price         = $product->get_price();
	$regular_price = $product->get_regular_price();
	// WooCommerce represents unset prices as empty strings.
	if ( '' === $price ) {
		return null;
	}
	if ( '' === $regular_price ) {
		return null;
	}
	if ( ! is_numeric( $price ) ) {
		throw new \RuntimeException( 'Outlet product price is invalid.' );
	}
	if ( ! is_numeric( $regular_price ) ) {
		throw new \RuntimeException( 'Outlet product regular price is invalid.' );
	}
	$discount = $regular_price > 0 && $price < $regular_price ? ( 1 - $price / $regular_price ) * 100 : 0;

	// Allow tiny floating-point errors at discount tier boundaries.
	$epsilon = 1e-9;

	$tiers = get_discount_tiers();
	$match = null;
	$best  = -INF;
	foreach ( $tiers as $threshold ) {
		if ( $discount + $epsilon < $threshold ) {
			continue;
		}
		if ( $threshold <= $best ) {
			continue;
		}
		$match = $threshold;
		$best  = $threshold;
	}

	return $match;
}

/**
 * Classify the discount tier for variable products.
 *
 * Ensures that all visible variations have the same discount tier, otherwise returns null due to the mismatch.
 *
 * @param \WC_Product_Variable $product Product to classify.
 * @return int|null Shared variation discount tier, or null when tiers differ.
 * @throws \RuntimeException If a variation cannot be retrieved or classified.
 * @internal
 */
function classify_discount_tier_for_variable_product( \WC_Product_Variable $product ): ?int {
	// Determine the visible variations from the price lookup.
	$prices        = $product->get_variation_prices( false );
	$variation_ids = array_keys( $prices['price'] );

	$tier = null;
	foreach ( $variation_ids as $variation_id ) {
		$variation = wc_get_product( $variation_id );
		if ( ! $variation ) {
			throw new \RuntimeException( 'Outlet product variation could not be retrieved.' );
		}
		$variation_tier = classify_discount_tier( $variation );
		if ( null === $variation_tier ) {
			return null;
		}
		if ( null !== $tier && $tier !== $variation_tier ) {
			return null;
		}
		$tier = $variation_tier;
	}
	return $tier;
}
