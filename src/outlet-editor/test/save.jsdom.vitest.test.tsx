/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { act, renderHook } from '@testing-library/react';
import apiFetch from '@wordpress/api-fetch';
import { expect, test, vi } from 'vitest';
import useEditorChanges from '../useEditorChanges';

vi.mock( '@wordpress/api-fetch', () => ( { default: vi.fn() } ) );

test( 'saves selected feature changes in order before the page and warns about pending changes', async () => {
	// Arrange.
	const sources = [ 'skipped', 'feature', 'page' ].map( ( id ) => ( {
		id,
		label: id,
		changes: [ { id: 'one', label: id } ],
		save: async ( ids: string[] ) => {
			await apiFetch( {
				path: `/${ id }`,
				method: 'POST',
				data: { ids },
			} );
		},
	} ) );
	vi.mocked( apiFetch ).mockReset();
	vi.mocked( apiFetch ).mockResolvedValue( {} );
	const { result } = renderHook( () => useEditorChanges( sources ) );
	const event = new Event( 'beforeunload', { cancelable: true } );
	window.dispatchEvent( event );

	// Act.
	await act( async () => {
		await result.current.save( { feature: [ 'one' ], page: [ 'one' ] } );
	} );

	// Assert.
	expect(
		vi.mocked( apiFetch ).mock.calls.map( ( [ options ] ) => options.path )
	).toEqual( [ '/feature', '/page' ] );
	expect( event.defaultPrevented ).toBe( true );
	expect( result.current.busy ).toBe( false );
	expect( result.current.failed ).toBe( false );
} );

test( 'stops after partial success, retains pending changes and rejects another save', async () => {
	// Arrange.
	const sources = [ 'first', 'second', 'page' ].map( ( id ) => ( {
		id,
		label: id,
		changes: [ { id: 'one', label: id } ],
		save: async () => {
			await apiFetch( { path: `/${ id }`, method: 'POST' } );
		},
	} ) );
	const log = vi.spyOn( console, 'error' ).mockImplementation( () => {} );
	vi.mocked( apiFetch ).mockReset();
	vi.mocked( apiFetch )
		.mockResolvedValueOnce( {} )
		.mockRejectedValueOnce( { message: 'Save denied.' } );
	const { result } = renderHook( () => useEditorChanges( sources ) );

	// Act.
	await act( async () => {
		expect(
			await result.current.save( {
				first: [ 'one' ],
				second: [ 'one' ],
				page: [ 'one' ],
			} )
		).toBe( false );
	} );

	// Assert.
	expect(
		vi.mocked( apiFetch ).mock.calls.map( ( [ options ] ) => options.path )
	).toEqual( [ '/first', '/second' ] );
	expect( result.current.failed ).toBe( true );
	expect( result.current.dirty ).toBe( true );
	expect( result.current.error ).toBe( 'Save denied.' );
	await expect( result.current.save( { page: [ 'one' ] } ) ).rejects.toThrow(
		'Outlet editor cannot save after a failed request.'
	);
	expect( apiFetch ).toHaveBeenCalledTimes( 2 );
	expect( log ).toHaveBeenCalledWith(
		'Outlet editor changes could not be saved.',
		{ message: 'Save denied.' }
	);
	log.mockRestore();
} );

test( 'rejects a concurrent save without sending another request', async () => {
	// Arrange.
	let complete: () => void = vi.fn();
	vi.mocked( apiFetch ).mockReset();
	vi.mocked( apiFetch ).mockImplementation(
		() =>
			new Promise( ( resolve ) => {
				complete = () => resolve( {} );
			} )
	);
	const source = {
		id: 'page',
		label: 'Page',
		changes: [ { id: 'one', label: 'Page' } ],
		save: async () => {
			await apiFetch( { path: '/page', method: 'POST' } );
		},
	};
	const { result } = renderHook( () => useEditorChanges( [ source ] ) );

	// Act.
	await act( async () => {
		const pending = result.current.save( { page: [ 'one' ] } );
		await expect(
			result.current.save( { page: [ 'one' ] } )
		).rejects.toThrow( 'Outlet editor changes are already being saved.' );
		complete();
		expect( await pending ).toBe( true );
	} );

	// Assert.
	expect( apiFetch ).toHaveBeenCalledTimes( 1 );
	expect( result.current.failed ).toBe( false );
} );
