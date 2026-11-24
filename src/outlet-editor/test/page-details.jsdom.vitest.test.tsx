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
	const view = render( <PageDetails { ...result.current } /> );
	// Assert.
	expect( apiFetch ).toHaveBeenCalledWith( {
		path: '/wp/v2/pages/39?context=edit',
		parse: false,
	} );
	expect( view.getByRole( 'heading', { name: 'Clearance' } ) ).toBeVisible();
	expect( view.getByText( 'Draft', { exact: true } ) ).toBeVisible();
	expect( view.getByText( 'clearance', { exact: true } ) ).toBeVisible();
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
	} );
	// Assert.
	expect( result.current.title ).toBe( 'Clearance' );
	expect( result.current.slug ).toBe( 'sale' );
	expect( result.current.dirty ).toBe( true );
	expect( result.current.page ).toEqual( page );
	expect( apiFetch ).toHaveBeenCalledTimes( 1 );
	act( () => {
		result.current.setTitle( 'Outlet' );
		result.current.setSlug( 'outlet' );
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
