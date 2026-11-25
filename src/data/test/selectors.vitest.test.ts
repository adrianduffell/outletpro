/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { expect, test } from 'vitest';
import { createReduxStore, createRegistry } from '@wordpress/data';
import { store } from '../index';

type Settings = Record< string, unknown >;

function createEditorRegistry(
	settings: Settings,
	postEditorSettings?: Settings
) {
	const registry = createRegistry();
	const editor = createReduxStore( 'core/block-editor', {
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
		selectors: { getSettings: ( state: Settings ) => state },
	} );
	registry.register( editor );
	const postEditor = createReduxStore( 'core/editor', {
		reducer: (
			state = postEditorSettings ?? {},
			action: { type: string; settings?: Settings }
		) => action.settings ?? state,
		actions: {
			updateEditorSettings: ( value: Settings ) => ( {
				type: 'UPDATE_EDITOR_SETTINGS',
				settings: value,
			} ),
		},
		selectors: { getEditorSettings: ( state: Settings ) => state },
	} );
	if ( postEditorSettings !== undefined ) {
		registry.register( postEditor );
	}
	registry.register( store );
	return { registry, editor, postEditor };
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

test( 'selects facets from Page editor settings when block editor settings are missing', () => {
	// Arrange.
	const settings = {
		outletproPriceTiers: [ { id: 12, name: 'Under €250' } ],
		outletproDiscountTiers: [ { id: 34, name: '45% off' } ],
		outletproProductCategories: [
			{ id: 3, name: 'Zebra' },
			{ id: 1, name: 'Alpha' },
		],
	};
	const { registry } = createEditorRegistry( {}, settings );

	// Act.
	const prices = registry.select( store ).getPriceFacets();
	const discounts = registry.select( store ).getDiscountFacets();
	const categories = registry.select( store ).getProductCategories();

	// Assert.
	expect( prices ).toEqual( settings.outletproPriceTiers );
	expect( discounts ).toEqual( settings.outletproDiscountTiers );
	expect( categories ).toEqual( settings.outletproProductCategories );
} );

test( 'refreshes fallback facets when Page editor settings change', () => {
	// Arrange.
	const { registry, postEditor } = createEditorRegistry(
		{},
		{
			outletproPriceTiers: [ { id: 12, name: 'Under €250' } ],
			outletproDiscountTiers: [ { id: 34, name: '45% off' } ],
			outletproProductCategories: [ { id: 1, name: 'Alpha' } ],
		}
	);
	registry.select( store ).getPriceFacets();
	registry.select( store ).getDiscountFacets();
	registry.select( store ).getProductCategories();
	const updated = {
		outletproPriceTiers: [ { id: 56, name: 'Under €500' } ],
		outletproDiscountTiers: [ { id: 78, name: '70% off' } ],
		outletproProductCategories: [ { id: 3, name: 'Zebra' } ],
	};

	// Act.
	registry.dispatch( postEditor ).updateEditorSettings( updated );
	const prices = registry.select( store ).getPriceFacets();
	const discounts = registry.select( store ).getDiscountFacets();
	const categories = registry.select( store ).getProductCategories();

	// Assert.
	expect( prices ).toEqual( updated.outletproPriceTiers );
	expect( discounts ).toEqual( updated.outletproDiscountTiers );
	expect( categories ).toEqual( updated.outletproProductCategories );
} );

test( 'prefers block editor facets when both editors provide settings', () => {
	// Arrange.
	const settings = {
		outletproPriceTiers: [ { id: 12, name: 'Under €250' } ],
		outletproDiscountTiers: [ { id: 34, name: '45% off' } ],
		outletproProductCategories: [ { id: 1, name: 'Alpha' } ],
	};
	const { registry } = createEditorRegistry( settings, {
		outletproPriceTiers: [ { id: 56, name: 'Under €500' } ],
		outletproDiscountTiers: [ { id: 78, name: '70% off' } ],
		outletproProductCategories: [ { id: 3, name: 'Zebra' } ],
	} );

	// Act.
	const prices = registry.select( store ).getPriceFacets();
	const discounts = registry.select( store ).getDiscountFacets();
	const categories = registry.select( store ).getProductCategories();

	// Assert.
	expect( prices ).toEqual( settings.outletproPriceTiers );
	expect( discounts ).toEqual( settings.outletproDiscountTiers );
	expect( categories ).toEqual( settings.outletproProductCategories );
} );

test( 'preserves empty block editor facets when Page editor settings contain facets', () => {
	// Arrange.
	const { registry } = createEditorRegistry(
		{
			outletproPriceTiers: [],
			outletproDiscountTiers: [],
			outletproProductCategories: [],
		},
		{
			outletproPriceTiers: [ { id: 12, name: 'Under €250' } ],
			outletproDiscountTiers: [ { id: 34, name: '45% off' } ],
			outletproProductCategories: [ { id: 1, name: 'Alpha' } ],
		}
	);

	// Act.
	const prices = registry.select( store ).getPriceFacets();
	const discounts = registry.select( store ).getDiscountFacets();
	const categories = registry.select( store ).getProductCategories();

	// Assert.
	expect( prices ).toEqual( [] );
	expect( discounts ).toEqual( [] );
	expect( categories ).toEqual( [] );
} );
