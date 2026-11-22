<?php
/**
 * Plain PHP functions with no WordPress/WooCommerce dependencies.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

namespace OutletPro;

/**
 * Return the nearest candidate for a given number.
 *
 * Highest value is selected on ties.
 *
 * @internal
 * @param float $value Value to round.
 * @param int   ...$candidates Candidate values.
 * @return int The nearest candidate.
 * @throws \InvalidArgumentException When no candidates are supplied.
 */
function nearest( float $value, int ...$candidates ): int {
	if ( empty( $candidates ) ) {
		throw new \InvalidArgumentException( 'Candidates must not be empty.' );
	}

	sort( $candidates, SORT_NUMERIC );
	$nearest = $candidates[0];
	foreach ( $candidates as $candidate ) {
		if ( abs( $candidate - $value ) > abs( $nearest - $value ) ) {
			continue;
		}
		$nearest = $candidate;
	}
	return $nearest;
}
