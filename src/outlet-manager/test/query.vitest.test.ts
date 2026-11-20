/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { expect, test } from 'vitest';
import type { View } from '@wordpress/dataviews/wp';
import {
	getErrorMessage,
	getNextView,
	getProductsPath,
	INITIAL_VIEW,
} from '../query';

test( 'maps DataViews state to a server query without paginating locally', () => {
	// Arrange.
	const view: View = {
		...INITIAL_VIEW,
		page: 3,
		search: 'ABC & Co',
		filters: [ { field: 'category', operator: 'is', value: 12 } ],
	};

	// Act.
	const path = getProductsPath( view, 'all' );

	// Assert.
	expect( path ).toBe(
		'/outletpro/v1/products?membership=all&page=3&per_page=25&search=ABC+%26+Co&order=asc&category=12'
	);
} );

test( 'rejects corrupted category values instead of coercing them', () => {
	// Arrange.
	const view: View = {
		...INITIAL_VIEW,
		filters: [ { field: 'category', operator: 'is', value: '12' } ],
	};

	// Act and assert.
	expect( () => getProductsPath( view, 'outlet' ) ).toThrow(
		'Invalid product category filter.'
	);
} );

test( 'resets pagination when a filter changes', () => {
	// Arrange.
	const previous = { ...INITIAL_VIEW, page: 3 };
	const next = { ...previous, search: 'shirt' };

	// Act.
	const result = getNextView( previous, next );

	// Assert.
	expect( result.page ).toBe( 1 );
	expect( previous.page ).toBe( 3 );
} );

test( 'preserves an explicit page change', () => {
	// Arrange.
	const next = { ...INITIAL_VIEW, page: 2 };

	// Act.
	const result = getNextView( INITIAL_VIEW, next );

	// Assert.
	expect( result.page ).toBe( 2 );
} );

test( 'uses the API error message when available', () => {
	// Arrange.
	const error = {
		code: 'permission_denied',
		message: 'Cannot edit this product.',
	};

	// Act.
	const result = getErrorMessage( error, 'Try again.' );

	// Assert.
	expect( result ).toBe( 'Cannot edit this product.' );
} );

test.each( [ null, undefined, 'network failure', { message: 12 } ] )(
	'uses a safe fallback for unexpected errors: %j',
	( error ) => {
		// Arrange.
		const fallback = 'Try again.';

		// Act.
		const result = getErrorMessage( error, fallback );

		// Assert.
		expect( result ).toBe( fallback );
	}
);
