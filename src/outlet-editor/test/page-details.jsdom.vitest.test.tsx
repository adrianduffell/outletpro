/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import {
	act,
	render,
	renderHook,
	waitFor,
	within,
} from '@testing-library/react';
import apiFetch from '@wordpress/api-fetch';
import { expect, test, vi } from 'vitest';
import PageDetails from '../PageDetails';
import useOutletPage from '../useOutletPage';
import { createEditorRegistry } from '../test-utils';

vi.mock( '@wordpress/api-fetch', () => ( { default: vi.fn() } ) );

test( 'loads page details through the authenticated WordPress edit context', async () => {
	// Arrange.
	const page = {
		id: 39,
		title: { raw: 'Clearance' },
		slug: 'clearance',
		status: 'draft',
		link: '/clearance/',
	};
	vi.mocked( apiFetch )
		.mockReset()
		.mockResolvedValueOnce( new Response( JSON.stringify( page ) ) );
	const { wrapper } = createEditorRegistry();
	// Act.
	const { result } = renderHook( () => useOutletPage( 39 ), { wrapper } );
	await waitFor( () => expect( result.current.page ).toEqual( page ) );
	const view = render(
		<PageDetails
			{ ...result.current }
			onTitleChange={ result.current.setTitle }
			onSlugChange={ result.current.setSlug }
			onStatusChange={ result.current.setStatus }
		/>
	);
	// Assert.
	expect( apiFetch ).toHaveBeenCalledWith( {
		path: '/wp/v2/pages/39?context=edit',
		parse: false,
	} );
	expect( view.getByRole( 'heading', { name: 'Clearance' } ) ).toBeVisible();
	expect( view.getByRole( 'button', { name: 'Draft' } ) ).toHaveTextContent(
		'Draft'
	);
	expect(
		view.getByRole( 'button', { name: 'clearance' } )
	).toHaveTextContent( 'clearance' );
	expect( view.getByRole( 'button', { name: 'Rename page' } ) ).toBeVisible();
} );

test( 'stages page edits without writing and clears dirty state when restored', async () => {
	// Arrange.
	const page = {
		id: 39,
		title: { raw: 'Outlet' },
		slug: 'outlet',
		status: 'draft',
		link: '/outlet/',
	};
	vi.mocked( apiFetch )
		.mockReset()
		.mockResolvedValueOnce( new Response( JSON.stringify( page ) ) );
	const { wrapper } = createEditorRegistry();
	const { result } = renderHook( () => useOutletPage( 39 ), { wrapper } );
	await waitFor( () => expect( result.current.page ).toEqual( page ) );
	// Act.
	act( () => {
		result.current.setTitle( 'Clearance' );
		result.current.setSlug( 'sale' );
		result.current.setStatus( 'publish' );
	} );
	// Assert.
	expect( result.current.title ).toBe( 'Clearance' );
	expect( result.current.slug ).toBe( 'sale' );
	expect( result.current.status ).toBe( 'publish' );
	expect( result.current.dirty ).toBe( true );
	expect( result.current.page ).toEqual( page );
	expect( apiFetch ).toHaveBeenCalledTimes( 1 );
	act( () => {
		result.current.setTitle( 'Outlet' );
		result.current.setSlug( 'outlet' );
		result.current.setStatus( 'draft' );
	} );
	expect( result.current.dirty ).toBe( false );
} );

test( 'saves publication status and date through core-data', async () => {
	// Arrange.
	const page = {
		id: 39,
		title: { raw: 'Outlet' },
		slug: 'outlet',
		status: 'draft',
		link: '/outlet/',
	};
	vi.mocked( apiFetch )
		.mockReset()
		.mockResolvedValueOnce( new Response( JSON.stringify( page ) ) )
		.mockResolvedValueOnce( { ...page, status: 'publish' } );
	const { wrapper } = createEditorRegistry();
	const { result } = renderHook( () => useOutletPage( 39 ), { wrapper } );
	await waitFor( () => expect( result.current.page ).toEqual( page ) );
	// Act.
	await act( () => result.current.save( 'publish' ) );
	// Assert.
	expect( apiFetch ).toHaveBeenLastCalledWith(
		expect.objectContaining( {
			path: '/wp/v2/pages/39',
			method: 'PUT',
			data: { id: 39, status: 'publish', date: expect.any( String ) },
		} )
	);
	expect( result.current.page?.status ).toBe( 'publish' );
	expect( result.current.dirty ).toBe( false );
} );

test( 'preserves the original publication date when saving a published page', async () => {
	// Arrange.
	const page = {
		id: 39,
		title: { raw: 'Outlet' },
		slug: 'outlet',
		status: 'publish',
		date: '2025-01-01T12:00:00',
		link: '/outlet/',
	};
	vi.mocked( apiFetch )
		.mockReset()
		.mockResolvedValueOnce( new Response( JSON.stringify( page ) ) )
		.mockResolvedValueOnce( { ...page, slug: 'sale' } );
	const { wrapper } = createEditorRegistry();
	const { result } = renderHook( () => useOutletPage( 39 ), { wrapper } );
	await waitFor( () => expect( result.current.page ).toEqual( page ) );
	// Act.
	act( () => result.current.setSlug( 'sale' ) );
	await act( () => result.current.save() );
	// Assert.
	expect( apiFetch ).toHaveBeenLastCalledWith(
		expect.objectContaining( {
			method: 'PUT',
			data: { id: 39, slug: 'sale' },
		} )
	);
	expect( result.current.page ).toEqual( { ...page, slug: 'sale' } );
} );

test( 'clears publication intent when restoring a draft after a failed save', async () => {
	// Arrange.
	const page = {
		id: 39,
		title: { raw: 'Outlet' },
		slug: 'outlet',
		status: 'draft',
		link: '/outlet/',
	};
	vi.mocked( apiFetch )
		.mockReset()
		.mockResolvedValueOnce( new Response( JSON.stringify( page ) ) )
		.mockRejectedValueOnce( new Error( 'Publication failed.' ) );
	const { wrapper } = createEditorRegistry();
	const { result } = renderHook( () => useOutletPage( 39 ), { wrapper } );
	await waitFor( () => expect( result.current.page ).toEqual( page ) );
	act( () => result.current.setStatus( 'publish' ) );
	// Act.
	await act( async () => {
		await expect( result.current.save() ).rejects.toThrow(
			'Publication failed.'
		);
	} );
	act( () => result.current.setStatus( 'draft' ) );
	// Assert.
	expect( result.current.dirty ).toBe( false );
	expect( result.current.status ).toBe( 'draft' );
	expect( result.current.page ).toEqual( page );
} );

test( 'shows a failed page load and does not offer a retry', async () => {
	// Arrange.
	const failure = { message: 'Page access denied.' };
	vi.mocked( apiFetch ).mockReset().mockRejectedValueOnce( failure );
	const { wrapper } = createEditorRegistry();
	// Act.
	const { result } = renderHook( () => useOutletPage( 39 ), { wrapper } );
	await waitFor( () =>
		expect( result.current.error ).toBe( failure.message )
	);
	const view = render( <PageDetails { ...result.current } /> );
	// Assert.
	expect(
		within( view.container ).getByText( failure.message )
	).toBeVisible();
	expect( view.queryByRole( 'button' ) ).not.toBeInTheDocument();
} );
