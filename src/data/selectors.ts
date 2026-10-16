/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { createRegistrySelector, createSelector } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';

export type SearchIndexFacet = { id: number; name: string };

const EMPTY_SEARCH_INDEX_FACETS: SearchIndexFacet[] = [];

function validateSearchIndexFacets( facets: unknown ): SearchIndexFacet[] {
	if ( facets === undefined ) {
		return EMPTY_SEARCH_INDEX_FACETS;
	}
	if ( ! Array.isArray( facets ) ) {
		throw new Error( 'Search index facets must be an array.' );
	}
	return facets.map( ( facet: unknown ) => {
		if ( typeof facet !== 'object' ) {
			throw new Error( 'Search index facet must be an object.' );
		}
		if ( null === facet ) {
			throw new Error( 'Search index facet must be an object.' );
		}
		if ( ! ( 'id' in facet ) ) {
			throw new Error( 'Search index facet ID is missing.' );
		}
		if ( typeof facet.id !== 'number' ) {
			throw new Error( 'Search index facet ID must be a number.' );
		}
		if ( ! Number.isInteger( facet.id ) ) {
			throw new Error( 'Search index facet ID must be an integer.' );
		}
		if ( facet.id < 1 ) {
			throw new Error( 'Search index facet ID must be positive.' );
		}
		if ( ! ( 'name' in facet ) ) {
			throw new Error( 'Search index facet name is missing.' );
		}
		if ( typeof facet.name !== 'string' ) {
			throw new Error( 'Search index facet name must be a string.' );
		}
		return { id: facet.id, name: facet.name };
	} );
}

function createSearchIndexFacetSelector(
	getSetting: ( settings: object ) => unknown
) {
	return createRegistrySelector( ( select ) => {
		const readSetting = () =>
			getSetting( select( editorStore ).getEditorSettings() );
		return createSelector(
			() => validateSearchIndexFacets( readSetting() ),
			() => [ readSetting() ]
		);
	} );
}

export const getPriceFacets = createSearchIndexFacetSelector( ( settings ) =>
	'outletproPriceTiers' in settings ? settings.outletproPriceTiers : undefined
);
export const getDiscountFacets = createSearchIndexFacetSelector(
	( settings ) =>
		'outletproDiscountTiers' in settings
			? settings.outletproDiscountTiers
			: undefined
);
