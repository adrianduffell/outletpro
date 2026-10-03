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
