/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { fireEvent, render, waitFor, within } from '@testing-library/react';
import apiFetch from '@wordpress/api-fetch';
import { expect, test, vi } from 'vitest';
import Products from '../Products';
import OutletEditorScreen from '../../OutletEditorScreen';
import { createEditorRegistry } from '../../test-utils';
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
test( 'shows query failures', async () => {
	// Arrange.
	vi.mocked( apiFetch ).mockReset();
	vi.mocked( apiFetch ).mockRejectedValue(
		new Error( 'The network is unavailable.' )
	);

	// Act.
	const view = render( <Products />, { wrapper: ProductsRegistry } );

	// Assert.
	expect(
		await within( view.container ).findByText(
			'Products could not be loaded.'
		)
	).toBeVisible();
} );

test( 'requests outlet products when the outlet-only filter is enabled', async () => {
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
	await waitFor( () =>
		expect( apiFetch ).toHaveBeenLastCalledWith(
			expect.objectContaining( {
				path: expect.stringContaining( 'outletpro=true' ),
			} )
		)
	);
} );

test( 'keeps a pending query in the loading state until its empty response arrives', async () => {
	// Arrange.
	let complete: ( response: Response ) => void = vi.fn();
	vi.mocked( apiFetch ).mockReset();
	const response = new Promise< Response >( ( resolve ) => {
		complete = resolve;
	} );
	vi.mocked( apiFetch ).mockReturnValue( response );
	const view = render( <Products />, { wrapper: ProductsRegistry } );
	expect(
		within( view.container ).queryByText( 'Products could not be loaded.' )
	).not.toBeInTheDocument();
	expect( view.queryByText( 'No products found' ) ).not.toBeInTheDocument();

	// Act.
	complete( productsResponse( [] ) );

	// Assert.
	expect( await view.findByText( 'No products found' ) ).toBeVisible();
} );

test.each( [
	[ 'GET', 'You do not have permission to manage products.' ],
	[ null, 'Product editing permission could not be loaded.' ],
] )( 'shows the native permission result %s', async ( allow, message ) => {
	// Arrange.
	vi.mocked( apiFetch ).mockReset();
	vi.mocked( apiFetch ).mockImplementation( async ( options ) => {
		if ( options.method === 'OPTIONS' ) {
			if ( allow === null ) {
				throw new Error( 'Permission could not be loaded.' );
			}
			return new Response( null, { headers: { Allow: allow } } );
		}
		return new Response(
			JSON.stringify( {
				id: 39,
				title: { raw: 'Outlet' },
				slug: 'outlet',
				status: 'draft',
				link: '/outlet/',
			} )
		);
	} );
	const { wrapper } = createEditorRegistry( {
		outletpro_markdown_levels: [],
	} );

	// Act.
	const view = render(
		<OutletEditorScreen config={ { pageId: 39, canPublish: true } } />,
		{ wrapper }
	);

	// Assert.
	expect(
		await within( view.container ).findByText( message )
	).toBeVisible();
} );
