/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { expect, test } from 'vitest';
import { formatPrice } from '../currency';

test.each( [
	[ '£', '%1$s%2$s', ',', '£1,250' ],
	[ '€', '%2$s\u00a0%1$s', '.', '1.250\u00a0€' ],
	[ '$', '%1$s %2$s', '', '$ 1250' ],
	[ '¥', '%2$s%1$s', '$&', '1$&250¥' ],
] )(
	'formats whole prices with %s',
	( symbol, priceFormat, thousandSeparator, expected ) => {
		// Arrange.
		const currency = { symbol, priceFormat, thousandSeparator };
		// Act.
		const formatted = formatPrice( 1250, currency );
		// Assert.
		expect( formatted ).toBe( expected );
	}
);
