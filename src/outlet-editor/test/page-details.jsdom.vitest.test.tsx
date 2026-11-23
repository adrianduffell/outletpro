/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { render, renderHook, waitFor, within } from '@testing-library/react';
import apiFetch from '@wordpress/api-fetch';
import { expect, test, vi } from 'vitest';
import PageDetails from '../PageDetails';
import useOutletPage from '../useOutletPage';

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
	vi.mocked( apiFetch ).mockResolvedValueOnce( page );

	// Act.
	const { result, unmount } = renderHook( () => useOutletPage( 39 ) );
	await waitFor( () => expect( result.current.page ).toEqual( page ) );
	const view = render( <PageDetails { ...result.current } /> );

	// Assert.
	expect( apiFetch ).toHaveBeenLastCalledWith( {
		path: '/wp/v2/pages/39?context=edit',
		signal: expect.any( AbortSignal ),
	} );
	expect( view.getByRole( 'heading', { name: 'Clearance' } ) ).toBeVisible();
	expect( view.getByText( 'Draft', { exact: true } ) ).toBeVisible();
	expect( view.getByText( 'clearance', { exact: true } ) ).toBeVisible();
	unmount();
	expect( vi.mocked( apiFetch ).mock.lastCall?.[ 0 ].signal?.aborted ).toBe(
		true
	);
} );

test( 'shows a failed page load and does not offer a retry', async () => {
	// Arrange.
	const failure = { message: 'Page access denied.' };
	const log = vi.spyOn( console, 'error' ).mockImplementation( () => {} );
	vi.mocked( apiFetch ).mockRejectedValueOnce( failure );

	// Act.
	const { result } = renderHook( () => useOutletPage( 39 ) );
	await waitFor( () =>
		expect( result.current.error ).toBe( failure.message )
	);
	const view = render( <PageDetails { ...result.current } /> );

	// Assert.
	expect(
		within( view.container ).getByText( failure.message )
	).toBeVisible();
	expect( view.queryByRole( 'button' ) ).not.toBeInTheDocument();
	expect( log ).toHaveBeenCalledWith(
		'Outlet page details could not be retrieved.',
		failure
	);
	log.mockRestore();
} );
