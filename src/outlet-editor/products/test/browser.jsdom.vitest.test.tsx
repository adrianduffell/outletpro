/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { fireEvent, render, waitFor, within } from '@testing-library/react';
import apiFetch from '@wordpress/api-fetch';
import { expect, test, vi } from 'vitest';
import Products from '../Products';
import OutletEditorScreen from '../../OutletEditorScreen';
import {
	createProduct,
	productsResponse,
	ProductsRegistry,
} from '../../../data/products/test-utils/store-fixtures';

vi.mock( '@wordpress/api-fetch', () => ( { default: vi.fn() } ) );
vi.hoisted( () => {
	vi.stubGlobal( 'ResizeObserver', function ResizeObserver() {
		return { observe() {}, unobserve() {}, disconnect() {} };
	} );
} );

test( 'renders product rows and searches names or SKUs on the server', async () => {
	// Arrange.
	vi.mocked( apiFetch ).mockReset();
	vi.mocked( apiFetch ).mockImplementation( async () =>
		productsResponse( [ createProduct() ] )
	);
	const view = render( <Products />, { wrapper: ProductsRegistry } );
	expect( await view.findByRole( 'row', { name: /Hat HAT/ } ) ).toBeVisible();
	// Act.
	fireEvent.change( view.getByRole( 'searchbox' ), {
		target: { value: 'SKU-123' },
	} );
	// Assert.
	await waitFor( () =>
		expect( apiFetch ).toHaveBeenLastCalledWith(
			expect.objectContaining( {
				path: expect.stringContaining( 'search_name_or_sku=SKU-123' ),
			} )
		)
	);
} );
test( 'shows query failures without offering a retry', async () => {
	// Arrange.
	vi.mocked( apiFetch ).mockReset();
	vi.mocked( apiFetch ).mockImplementation( async () => {
		throw new Response(
			JSON.stringify( { message: 'The network is unavailable.' } ),
			{ status: 500 }
		);
	} );

	// Act.
	const view = render( <Products />, { wrapper: ProductsRegistry } );

	// Assert.
	expect(
		await within( view.container ).findByText(
			'Products could not be loaded.'
		)
	).toBeVisible();
	expect(
		view.queryByRole( 'button', { name: 'Retry' } )
	).not.toBeInTheDocument();
	expect( view.queryByRole( 'table' ) ).not.toBeInTheDocument();
	expect( apiFetch ).toHaveBeenCalledTimes( 1 );
} );

test( 'shows an empty state when no outlet products match', async () => {
	// Arrange.
	vi.mocked( apiFetch ).mockReset();
	vi.mocked( apiFetch ).mockImplementation( async () =>
		productsResponse( [] )
	);
	const view = render( <Products />, { wrapper: ProductsRegistry } );
	await view.findByText( 'No products found' );

	// Act.
	fireEvent.click(
		view.getByRole( 'checkbox', { name: 'Show outlet products only' } )
	);

	// Assert.
	expect( await view.findByText( 'No products found' ) ).toBeVisible();
	expect(
		within( view.container ).queryByText( 'Products could not be loaded.' )
	).not.toBeInTheDocument();
	await waitFor( () => expect( apiFetch ).toHaveBeenCalledTimes( 2 ) );
	expect(
		new URL(
			vi.mocked( apiFetch ).mock.lastCall?.[ 0 ].path ?? '',
			'http://localhost'
		).searchParams.get( 'outletpro' )
	).toBe( 'true' );
} );

test( 'keeps a pending query in the loading state until its empty response arrives', async () => {
	// Arrange.
	let complete: ( response: Response ) => void = vi.fn();
	vi.mocked( apiFetch ).mockReset();
	vi.mocked( apiFetch ).mockImplementation(
		() =>
			new Promise< Response >( ( resolve ) => {
				complete = resolve;
			} )
	);
	const view = render( <Products />, { wrapper: ProductsRegistry } );
	await waitFor( () => expect( apiFetch ).toHaveBeenCalledTimes( 1 ) );
	expect(
		within( view.container ).queryByText( 'Products could not be loaded.' )
	).not.toBeInTheDocument();
	expect( view.queryByText( 'No products found' ) ).not.toBeInTheDocument();

	// Act.
	complete( productsResponse( [] ) );

	// Assert.
	expect( await view.findByText( 'No products found' ) ).toBeVisible();
	expect(
		within( view.container ).queryByText( 'Products could not be loaded.' )
	).not.toBeInTheDocument();
} );

test.each( [
	[ 'GET', 'You do not have permission to manage products.' ],
	[ null, 'Product editing permission could not be loaded.' ],
] )( 'shows the native permission result %s', async ( allow, message ) => {
	// Arrange.
	vi.stubGlobal(
		'matchMedia',
		vi.fn( () => ( { matches: true } ) )
	);
	vi.mocked( apiFetch ).mockReset();
	vi.mocked( apiFetch ).mockImplementation( async ( options ) => {
		if ( options.method === 'OPTIONS' ) {
			if ( allow === null ) {
				throw new Error( 'Permission could not be loaded.' );
			}
			return new Response( null, { headers: { Allow: allow } } );
		}
		return {
			id: 39,
			title: { raw: 'Outlet' },
			slug: 'outlet',
			status: 'draft',
			link: '/outlet/',
		};
	} );

	// Act.
	const view = render(
		<OutletEditorScreen config={ { pageId: 39, canPublish: true } } />,
		{ wrapper: ProductsRegistry }
	);

	// Assert.
	expect(
		await within( view.container ).findByText( message )
	).toBeVisible();
} );
