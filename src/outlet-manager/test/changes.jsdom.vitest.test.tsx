/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { act, renderHook } from '@testing-library/react';
import apiFetch from '@wordpress/api-fetch';
import { expect, test, vi } from 'vitest';
import useProductChanges from '../useProductChanges';

vi.mock( '@wordpress/api-fetch', () => ( { default: vi.fn() } ) );

test( 'queues edits without requests and drops edits reverted to the original value', () => {
	// Arrange.
	const product = { id: 1, name: 'Hat', outlet: false, canEdit: true };
	const { result } = renderHook( useProductChanges );
	const requestCount = vi.mocked( apiFetch ).mock.calls.length;

	// Act.
	act( () => result.current.stage( [ product ], true ) );

	// Assert.
	expect( result.current.changes ).toEqual( [
		{ id: 1, name: 'Hat', original: false, outlet: true },
	] );
	expect( vi.mocked( apiFetch ).mock.calls ).toHaveLength( requestCount );
	act( () =>
		result.current.stage( [ { ...product, outlet: true } ], false )
	);
	expect( result.current.changes ).toEqual( [] );
} );

test( 'saves only selected products and keeps unticked changes queued', async () => {
	// Arrange.
	const { result } = renderHook( useProductChanges );
	act( () =>
		result.current.stage(
			[
				{ id: 1, name: 'Hat', outlet: false, canEdit: true },
				{ id: 2, name: 'Scarf', outlet: false, canEdit: true },
			],
			true
		)
	);
	vi.mocked( apiFetch ).mockResolvedValueOnce( {
		results: [ { id: 1, outlet: true } ],
	} );
	const onError = vi.fn();

	// Act.
	await act( () => result.current.save( onError, [ 1 ] ) );

	// Assert.
	expect( apiFetch ).toHaveBeenLastCalledWith( {
		path: '/outletpro/v1/products/batch',
		method: 'POST',
		data: { ids: [ 1 ], outlet: true },
	} );
	expect( result.current.changes ).toEqual( [
		{ id: 2, name: 'Scarf', original: false, outlet: true },
	] );
	expect( onError ).not.toHaveBeenCalled();
} );

test( 'ignores products that cannot be edited', () => {
	// Arrange.
	const { result } = renderHook( useProductChanges );

	// Act.
	act( () =>
		result.current.stage(
			[ { id: 2, name: 'Hat', outlet: false, canEdit: false } ],
			true
		)
	);

	// Assert.
	expect( result.current.changes ).toEqual( [] );
} );

test( 'saves includes and removals separately and retains only failed edits for retry', async () => {
	// Arrange.
	const { result } = renderHook( useProductChanges );
	const onError = vi.fn();
	act( () =>
		result.current.stage(
			[
				{ id: 1, name: 'Hat', outlet: false, canEdit: true },
				{ id: 2, name: 'Scarf', outlet: false, canEdit: true },
			],
			true
		)
	);
	act( () =>
		result.current.stage(
			[ { id: 3, name: 'Socks', outlet: true, canEdit: true } ],
			false
		)
	);
	vi.mocked( apiFetch )
		.mockResolvedValueOnce( {
			results: [
				{ id: 1, outlet: true },
				{ id: 2, error: 'Permission denied.' },
			],
		} )
		.mockResolvedValueOnce( { results: [ { id: 3, outlet: false } ] } );

	// Act.
	let saved;
	await act( async () => {
		saved = await result.current.save( onError );
	} );

	// Assert.
	expect( saved ).toBe( false );
	expect( apiFetch ).toHaveBeenCalledWith( {
		path: '/outletpro/v1/products/batch',
		method: 'POST',
		data: { ids: [ 1, 2 ], outlet: true },
	} );
	expect( apiFetch ).toHaveBeenLastCalledWith( {
		path: '/outletpro/v1/products/batch',
		method: 'POST',
		data: { ids: [ 3 ], outlet: false },
	} );
	expect( onError ).toHaveBeenCalledWith( 'Scarf: Permission denied.' );
	expect( result.current.changes.map( ( change ) => change.id ) ).toEqual( [
		2,
	] );
	expect( result.current.committed.map( ( change ) => change.id ) ).toEqual( [
		1, 3,
	] );
	vi.mocked( apiFetch ).mockResolvedValueOnce( {
		results: [ { id: 2, outlet: true } ],
	} );
	await act( async () => {
		saved = await result.current.save( onError );
	} );
	expect( saved ).toBe( true );
	expect( result.current.changes ).toEqual( [] );
} );

test( 'splits large queues into batches of at most 100 products', async () => {
	// Arrange.
	const { result } = renderHook( useProductChanges );
	const products = Array.from( { length: 101 }, ( _, index ) => ( {
		id: index + 1,
		name: 'Hat',
		outlet: false,
		canEdit: true,
	} ) );
	act( () => result.current.stage( products, true ) );
	vi.mocked( apiFetch )
		.mockResolvedValueOnce( {
			results: products
				.slice( 0, 100 )
				.map( ( product ) => ( { id: product.id, outlet: true } ) ),
		} )
		.mockResolvedValueOnce( { results: [ { id: 101, outlet: true } ] } );
	const onError = vi.fn();

	// Act.
	await act( () => result.current.save( onError ) );

	// Assert.
	expect( apiFetch ).toHaveBeenCalledWith(
		expect.objectContaining( {
			data: {
				ids: products.slice( 0, 100 ).map( ( product ) => product.id ),
				outlet: true,
			},
		} )
	);
	expect( apiFetch ).toHaveBeenLastCalledWith(
		expect.objectContaining( { data: { ids: [ 101 ], outlet: true } } )
	);
	expect( onError ).not.toHaveBeenCalled();
	expect( result.current.changes ).toEqual( [] );
} );

test( 'logs network failures and keeps the queue for retry', async () => {
	// Arrange.
	const log = vi.spyOn( console, 'error' ).mockImplementation( () => {} );
	const { result } = renderHook( useProductChanges );
	act( () =>
		result.current.stage(
			[ { id: 1, name: 'Hat', outlet: false, canEdit: true } ],
			true
		)
	);
	vi.mocked( apiFetch ).mockRejectedValueOnce( {
		message: 'Connection lost.',
	} );
	const onError = vi.fn();

	// Act.
	await act( () => result.current.save( onError ) );

	// Assert.
	expect( onError ).toHaveBeenCalledWith( 'Connection lost.' );
	expect( result.current.changes ).toHaveLength( 1 );
	expect( log ).toHaveBeenCalledWith(
		'Outlet membership could not be updated.',
		expect.anything()
	);
	log.mockRestore();
} );

test( 'keeps edits when a batch omits a requested product', async () => {
	// Arrange.
	const { result } = renderHook( useProductChanges );
	act( () =>
		result.current.stage(
			[ { id: 1, name: 'Hat', outlet: false, canEdit: true } ],
			true
		)
	);
	vi.mocked( apiFetch ).mockResolvedValueOnce( { results: [] } );
	const onError = vi.fn();

	// Act.
	await act( () => result.current.save( onError ) );

	// Assert.
	expect( onError ).toHaveBeenCalledWith(
		'Hat: Product could not be saved. Try again.'
	);
	expect( result.current.changes ).toHaveLength( 1 );
} );
