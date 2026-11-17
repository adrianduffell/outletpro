/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { expect, test, vi } from 'vitest';
import { createRegistry } from '@wordpress/data';
import { store as blockEditorStore } from '@wordpress/block-editor';
import { store } from '../index';

vi.hoisted( () => {
	Object.defineProperty( window, 'matchMedia', {
		value: () => ( {
			matches: false,
			addListener() {},
			removeListener() {},
			addEventListener() {},
			removeEventListener() {},
		} ),
	} );
} );

test( 'reads navigation settings in a Widgets registry without the Page editor store', () => {
	// Arrange.
	const registry = createRegistry();
	registry.register( blockEditorStore );
	registry.register( store );
	const categories = [ { id: 1, name: 'Clothing' } ];
	const prices = [ { id: 2, name: 'Under $25' } ];
	const discounts = [ { id: 3, name: '50% off' } ];
	registry.dispatch( 'core/block-editor' ).updateSettings( {
		outletproProductCategories: categories,
		outletproPriceTiers: prices,
		outletproDiscountTiers: discounts,
	} );

	// Act.
	const result = registry.select( store );

	// Assert.
	expect( result.getProductCategories() ).toEqual( categories );
	expect( result.getPriceFacets() ).toEqual( prices );
	expect( result.getDiscountFacets() ).toEqual( discounts );
} );
