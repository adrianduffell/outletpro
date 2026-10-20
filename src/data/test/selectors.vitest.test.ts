/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { expect, test, vi } from 'vitest';
import { createReduxStore, createRegistry } from '@wordpress/data';
import { store } from '../index';

vi.mock( '@wordpress/editor', () => ( {
	store: { name: 'core/editor' },
} ) );

type Settings = Record< string, unknown >;

function createEditorRegistry( settings: Settings ) {
	const registry = createRegistry();
	const editor = createReduxStore( 'core/editor', {
		reducer: (
			state = settings,
			action: { type: string; settings?: Settings }
		) => action.settings ?? state,
		actions: {
			updateSettings: ( value: Settings ) => ( {
				type: 'UPDATE_SETTINGS',
				settings: value,
			} ),
		},
		selectors: { getEditorSettings: ( state: Settings ) => state },
	} );
	registry.register( editor );
	registry.register( store );
	return { registry, editor };
}

test( 'selects price facets from editor settings', () => {
	// Arrange.
	const facets = [ { id: 12, name: 'Under €250' } ];
	const { registry } = createEditorRegistry( {
		outletproPriceTiers: facets,
	} );

	// Act.
	const result = registry.select( store ).getPriceFacets();

	// Assert.
	expect( result ).toEqual( facets );
} );

test( 'selects discount facets from editor settings', () => {
	// Arrange.
	const facets = [ { id: 34, name: '45% off' } ];
	const { registry } = createEditorRegistry( {
		outletproDiscountTiers: facets,
	} );

	// Act.
	const result = registry.select( store ).getDiscountFacets();

	// Assert.
	expect( result ).toEqual( facets );
} );

test( 'returns stable empty arrays for missing settings', () => {
	// Arrange.
	const { registry } = createEditorRegistry( {} );

	// Act.
	const prices = registry.select( store ).getPriceFacets();
	const discounts = registry.select( store ).getDiscountFacets();

	// Assert.
	expect( prices ).toEqual( [] );
	expect( registry.select( store ).getPriceFacets() ).toBe( prices );
	expect( discounts ).toEqual( [] );
	expect( registry.select( store ).getDiscountFacets() ).toBe( discounts );
} );

test( 'keeps facet references when unrelated settings change', () => {
	// Arrange.
	const settings = {
		outletproPriceTiers: [ { id: 12, name: 'Under €250' } ],
		outletproDiscountTiers: [ { id: 34, name: '45% off' } ],
	};
	const { registry, editor } = createEditorRegistry( settings );
	const prices = registry.select( store ).getPriceFacets();
	const discounts = registry.select( store ).getDiscountFacets();

	// Act.
	registry
		.dispatch( editor )
		.updateSettings( { ...settings, unrelated: true } );

	// Assert.
	expect( registry.select( store ).getPriceFacets() ).toBe( prices );
	expect( registry.select( store ).getDiscountFacets() ).toBe( discounts );
} );

test( 'refreshes facets when their setting changes', () => {
	// Arrange.
	const { registry, editor } = createEditorRegistry( {
		outletproPriceTiers: [ { id: 12, name: 'Under €250' } ],
	} );
	registry.select( store ).getPriceFacets();
	const updated = [ { id: 56, name: 'Under €500' } ];

	// Act.
	registry
		.dispatch( editor )
		.updateSettings( { outletproPriceTiers: updated } );

	// Assert.
	expect( registry.select( store ).getPriceFacets() ).toEqual( updated );
} );

test.each( [
	null,
	[ null ],
	[ 30 ],
	[ { id: '12', name: 'Under €250' } ],
	[ { id: 0, name: 'Under €250' } ],
	[ { id: 1.5, name: 'Under €250' } ],
	[ { name: 'Under €250' } ],
	[ { id: 12 } ],
	[ { id: 12, name: 250 } ],
] )( 'rejects malformed facet settings %j', ( facets ) => {
	// Arrange.
	const { registry } = createEditorRegistry( {
		outletproPriceTiers: facets,
	} );

	// Act.
	const selectFacets = () => registry.select( store ).getPriceFacets();

	// Assert.
	expect( selectFacets ).toThrow();
} );

test( 'selects product categories in editor setting order', () => {
	// Arrange.
	const categories = [
		{ id: 3, name: 'Zebra' },
		{ id: 1, name: 'Alpha' },
	];
	const { registry } = createEditorRegistry( {
		outletproProductCategories: categories,
	} );

	// Act.
	const result = registry.select( store ).getProductCategories();

	// Assert.
	expect( result ).toEqual( categories );
	expect( registry.select( store ).getProductCategories() ).toBe( result );
} );
