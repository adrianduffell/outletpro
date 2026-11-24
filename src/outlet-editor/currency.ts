/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

export function formatPrice(
	amount: number,
	currency: Window[ 'wc' ][ 'wcSettings' ][ 'CURRENCY' ]
): string {
	const grouped = amount
		.toString()
		.replace( /\B(?=(\d{3})+(?!\d))/g, () => currency.thousandSeparator );
	return currency.priceFormat.replace( /%[12]\$s/g, ( placeholder ) =>
		placeholder === '%1$s' ? currency.symbol : grouped
	);
}
