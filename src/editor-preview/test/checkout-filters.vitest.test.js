/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';
import { expect, test, vi } from 'vitest';

const script = readFileSync(
	new URL( '../../../assets/js/checkout-filters.js', import.meta.url ),
	'utf8'
);

test.each( [
	{
		description: 'marks real outlet products',
		classes: '',
		extensions: { outletpro: { is_outlet: true } },
		expected: 'outletpro-cart-item',
	},
	{
		description: 'preserves existing outlet product classes',
		classes: 'existing',
		extensions: { outletpro: { is_outlet: true } },
		expected: 'existing outletpro-cart-item',
	},
	{
		description: 'leaves regular products unmarked',
		classes: 'existing',
		extensions: { outletpro: { is_outlet: false } },
		expected: 'existing',
	},
	{
		description: 'leaves mock products unmarked',
		classes: '',
		extensions: {},
		expected: '',
	},
	{
		description: 'leaves products without extension data unmarked',
		classes: 'existing',
		extensions: undefined,
		expected: 'existing',
	},
	{
		description: 'requires a boolean outlet flag',
		classes: 'existing',
		extensions: { outletpro: { is_outlet: 'true' } },
		expected: 'existing',
	},
] )( '$description', ( { classes, extensions, expected } ) => {
	// Arrange.
	const registerCheckoutFilters = vi.fn();
	runInNewContext( script, {
		window: { wc: { blocksCheckout: { registerCheckoutFilters } } },
	} );
	const [ namespace, { cartItemClass } ] =
		registerCheckoutFilters.mock.calls[ 0 ];

	// Act.
	const result = cartItemClass( classes, extensions );

	// Assert.
	expect( namespace ).toBe( 'outletpro' );
	expect( result ).toBe( expected );
} );
