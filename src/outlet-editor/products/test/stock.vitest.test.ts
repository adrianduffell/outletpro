/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { expect, test } from 'vitest';
import { getStockLabel } from '../stock';

test.each( [
	[ 'instock', true, 8, 'In stock (8)' ],
	[ 'instock', false, null, 'In stock' ],
	[ 'instock', true, null, 'In stock' ],
	[ 'outofstock', true, 0, 'Out of stock' ],
	[ 'onbackorder', false, null, 'On backorder' ],
] )(
	'labels stock %s with managed quantity %j / %j',
	( stockStatus, manageStock, stockQuantity, label ) => {
		// Arrange.
		const product = {
			stock_status: stockStatus,
			manage_stock: manageStock,
			stock_quantity: stockQuantity,
		};
		// Act.
		const result = getStockLabel( product );
		// Assert.
		expect( result ).toBe( label );
	}
);
