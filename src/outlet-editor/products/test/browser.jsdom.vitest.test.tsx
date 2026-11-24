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
	expect( await view.findByRole( 'row', { name: /Hat/ } ) ).toBeVisible();
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

test( 'presents native product details and permits links according to REST permissions', async () => {
	// Arrange.
	const allowed = createProduct( {
		name: 'Alpha &amp; Beta',
		sku: 'SKU-1',
		total_sales: 1234,
		manage_stock: true,
		stock_quantity: 8,
		price_html:
			'<del aria-hidden="true">£18</del><span class="screen-reader-text">Original price: £18</span><ins>£12</ins>',
	} );
	const denied = createProduct( {
		id: 2,
		name: 'Restricted',
		_links: {
			self: [
				{
					href: '/wc/v3/products/2',
					targetHints: { allow: [ 'GET' ] },
				},
			],
		},
	} );
	const imageless = createProduct( { id: 3, name: 'No image', images: [] } );
	vi.mocked( apiFetch ).mockReset();
	vi.mocked( apiFetch ).mockImplementation( async () =>
		productsResponse( [ allowed, denied, imageless ] )
	);
	// Act.
	const view = render( <Products />, { wrapper: ProductsRegistry } );
	const row = await view.findByRole( 'row', { name: /Alpha & Beta/ } );
	// Assert.
	expect(
		within( row ).getByRole( 'link', { name: 'Alpha & Beta' } )
	).toHaveAttribute( 'href', 'post.php?post=1&action=edit' );
	expect( within( row ).getByText( 'SKU-1' ) ).toBeVisible();
	expect( within( row ).getByText( '1,234' ) ).toBeVisible();
	expect( within( row ).getByText( 'In stock (8)' ) ).toBeVisible();
	expect( row.querySelector( 'img' ) ).toHaveAttribute(
		'src',
		'/hat-small.jpg'
	);
	expect(
		( await view.findByRole( 'row', { name: /No image/ } ) ).querySelector(
			'img'
		)
	).not.toBeInTheDocument();
	expect( row.querySelector( 'del' ) ).toHaveAttribute(
		'aria-hidden',
		'true'
	);
	expect( row.querySelector( 'ins' ) ).toHaveTextContent( '£12' );
	expect(
		within(
			await view.findByRole( 'row', { name: /Restricted/ } )
		).queryByRole( 'link' )
	).not.toBeInTheDocument();
	expect( view.getAllByRole( 'columnheader' ) ).toHaveLength( 8 );
	expect( apiFetch ).toHaveBeenCalledTimes( 1 );
} );
