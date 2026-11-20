/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { expect, test } from 'vitest';
import { getStockLabel } from '../stock';

test.each( [
	{ status: 'instock', managed: true, quantity: 8, expected: 'In stock (8)' },
	{ status: 'instock', managed: false, quantity: 8, expected: 'In stock' },
	{ status: 'instock', managed: true, quantity: null, expected: 'In stock' },
	{ status: 'instock', managed: true, quantity: 0, expected: 'In stock (0)' },
	{
		status: 'instock',
		managed: true,
		quantity: 2.5,
		expected: 'In stock (2.5)',
	},
	{
		status: 'outofstock',
		managed: true,
		quantity: 0,
		expected: 'Out of stock',
	},
	{
		status: 'onbackorder',
		managed: true,
		quantity: -2,
		expected: 'On backorder',
	},
	{ status: 'custom', managed: true, quantity: 8, expected: 'custom' },
] )(
	'formats $status with stock management $managed and quantity $quantity',
	( { status, managed, quantity, expected } ) => {
		// Arrange.
		const product = {
			stock_status: status,
			manage_stock: managed,
			stock_quantity: quantity,
		};

		// Act.
		const label = getStockLabel( product );

		// Assert.
		expect( label ).toBe( expected );
	}
);
