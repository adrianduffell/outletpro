/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { expect, test } from 'vitest';
import { getProductRow, sanitizePrice } from '../presentation';
import { createProduct } from '../../../data/products/test-utils/store-fixtures';

test( 'derives presentation from native product fields and REST permissions', () => {
	// Arrange.
	const product = createProduct();
	// Act.
	const row = getProductRow(
		product,
		true,
		Date.parse( '2026-11-03T12:00:00Z' )
	);
	// Assert.
	expect( row ).toMatchObject( {
		image: '/hat-small.jpg',
		price: '£10',
		age_days: 2,
		canEdit: true,
	} );
} );

test.each( [ null, '', 'invalid' ] )(
	'shows missing age for date %j and no image when absent',
	( date ) => {
		// Arrange.
		const product = createProduct( { date_created_gmt: date, images: [] } );
		// Act.
		const row = getProductRow( product, false );
		// Assert.
		expect( row ).toMatchObject( {
			image: null,
			age_days: null,
			canEdit: false,
		} );
	}
);

test.each( [ { status: 'trash' }, { type: 'variation' } ] )(
	'disables edits for excluded products %j',
	( edits ) => {
		// Arrange.
		const product = createProduct( edits );
		// Act.
		const row = getProductRow( product, true );
		// Assert.
		expect( row.canEdit ).toBe( false );
	}
);

test( 'removes unsafe price markup while retaining accessible sale prices', () => {
	// Arrange.
	const html =
		'<del aria-hidden="true">£18</del><span class="screen-reader-text">Original price: £18</span><ins onclick="alert(1)">£12</ins><img src=x onerror="alert(1)"><script>alert(1)</script><a href="javascript:alert(1)">tax</a>';
	// Act.
	const result = sanitizePrice( html );
	// Assert.
	expect( result ).toBe(
		'<del aria-hidden="true">£18</del><span class="screen-reader-text">Original price: £18</span><ins>£12</ins>tax'
	);
} );

test( 'fails when membership is absent instead of assuming the product is excluded', () => {
	// Arrange.
	const product = createProduct( { outletpro: {} } );
	// Act and assert.
	expect( () => getProductRow( product, true ) ).toThrow(
		'Outlet product membership is unavailable.'
	);
} );
