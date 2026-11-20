/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { fireEvent, render, waitFor, within } from '@testing-library/react';
import apiFetch from '@wordpress/api-fetch';
import { expect, test, vi } from 'vitest';
import Products from '../Products';
import useProductChanges from '../useProductChanges';
import type { Product } from '../types';

vi.mock( '@wordpress/api-fetch', () => ( { default: vi.fn() } ) );
vi.hoisted( () => {
	Object.defineProperty( window, 'matchMedia', {
		writable: true,
		value: () => ( {
			matches: false,
			addListener() {},
			removeListener() {},
			addEventListener() {},
			removeEventListener() {},
		} ),
	} );
} );

function ProductsHarness() {
	const changes = useProductChanges();
	return <Products categories={ [] } changes={ changes } />;
}

test( 'renders real DataViews fields from the server response', async () => {
	// Arrange.
	const product: Product = {
		id: 1,
		name: 'Alpha &amp; Beta',
		sku: 'SKU-1',
		price: '&pound;10.00',
		stock_status: 'instock',
		image: '/product-thumbnail.jpg',
		total_sales: 1234,
		age_days: 37,
		manage_stock: true,
		stock_quantity: 8,
		status: 'publish',
		outlet: true,
		canEdit: true,
		editUrl: '/wp-admin/post.php?post=1',
		categories: [
			{ id: 3, name: 'Accessories' },
			{ id: 4, name: 'Clothing' },
		],
	};
	vi.mocked( apiFetch ).mockResolvedValue( {
		items: [ product ],
		totalItems: 1,
		totalPages: 1,
		totalOutletItems: 1,
	} );

	// Act.
	const view = render( <ProductsHarness /> );

	// Assert.
	expect(
		view.getByRole( 'heading', {
			name: 'Manage outlet products',
			level: 2,
		} )
	).toBeVisible();
	expect(
		view.getByRole( 'checkbox', { name: 'Show outlet products only' } )
	).not.toBeChecked();
	expect(
		( await view.findAllByRole( 'link', { name: 'Alpha & Beta' } ) )[ 0 ]
	).toBeVisible();
	expect( view.getByText( 'SKU-1' ) ).toBeVisible();
	expect( view.getByText( '£10.00' ) ).toBeVisible();
	expect( view.getByText( '1,234' ) ).toBeVisible();
	expect( view.getByText( '37' ) ).toBeVisible();
	expect( view.getByText( 'In stock (8)' ) ).toBeVisible();
	expect(
		view.queryByText( 'Accessories, Clothing' )
	).not.toBeInTheDocument();
	expect(
		view.getByRole( 'checkbox', { name: 'Include Alpha & Beta in outlet' } )
	).toBeChecked();
	expect(
		view.getByText( 'Select the products to display on the outlet page.' )
	).toBeVisible();
	const headers = view.getAllByRole( 'columnheader' );
	expect( headers[ 0 ] ).toHaveAccessibleName( 'Image' );
	expect( headers[ 1 ] ).toHaveTextContent( 'Product' );
	expect( headers[ 2 ] ).toHaveTextContent( 'SKU' );
	expect( headers[ 3 ] ).toHaveTextContent( 'Total Sales' );
	expect( headers[ 4 ] ).toHaveTextContent( 'Age (days)' );
	expect( headers[ 5 ] ).toHaveTextContent( 'Price' );
	expect( headers[ 6 ] ).toHaveTextContent( 'Stock' );
	expect( headers[ 7 ] ).toHaveTextContent( 'Outlet' );
	expect( headers ).toHaveLength( 8 );
	expect( view.container.querySelector( 'img' ) ).toHaveAttribute(
		'src',
		'/product-thumbnail.jpg'
	);
	const table = view.getByRole( 'table' );
	expect( within( table ).getAllByRole( 'checkbox' ) ).toHaveLength( 1 );
	expect(
		within( view.getAllByRole( 'row' )[ 1 ] ).getAllByRole( 'cell' )[ 7 ]
	).toContainElement(
		view.getByRole( 'checkbox', { name: 'Include Alpha & Beta in outlet' } )
	);
	expect( apiFetch ).toHaveBeenCalledWith(
		expect.objectContaining( {
			path: '/outletpro/v1/products?membership=all&page=1&per_page=25&search=&order=asc',
		} )
	);
} );

test( 'preserves sale price formatting and screen-reader descriptions', async () => {
	// Arrange.
	const product: Product = {
		id: 8,
		name: 'Sale product',
		sku: '',
		price: '<del aria-hidden="true">&pound;18.00</del> <span class="screen-reader-text">Original price was: &pound;18.00.</span><ins aria-hidden="true">&pound;12.00</ins><span class="screen-reader-text">Current price is: &pound;12.00.</span>',
		stock_status: 'instock',
		image: '/product-thumbnail.jpg',
		total_sales: 0,
		age_days: 0,
		manage_stock: false,
		stock_quantity: null,
		status: 'publish',
		outlet: true,
		canEdit: true,
		editUrl: '/product/8',
		categories: [],
	};
	vi.mocked( apiFetch ).mockResolvedValue( {
		items: [ product ],
		totalItems: 1,
		totalPages: 1,
		totalOutletItems: 1,
	} );

	// Act.
	const view = render( <ProductsHarness /> );
	const row = await view.findByRole( 'row', { name: /Sale product/ } );

	// Assert.
	expect( row.querySelector( 'del' ) ).toHaveTextContent( '£18.00' );
	expect( row.querySelector( 'ins' ) ).toHaveTextContent( '£12.00' );
	expect( row.querySelector( 'del' ) ).toHaveAttribute(
		'aria-hidden',
		'true'
	);
	expect(
		row.querySelectorAll( '.outletpro-manager-price .screen-reader-text' )
	).toHaveLength( 2 );
	expect(
		within( row ).getByText( 'Current price is: £12.00.' )
	).toHaveClass( 'screen-reader-text' );
} );

test( 'fetches server results when toggling the outlet-only filter', async () => {
	// Arrange.
	const product: Product = {
		id: 2,
		name: 'Selected product',
		sku: '',
		price: '',
		stock_status: 'instock',
		image: '/product-thumbnail.jpg',
		total_sales: 0,
		age_days: 0,
		manage_stock: false,
		stock_quantity: null,
		status: 'publish',
		outlet: true,
		canEdit: true,
		editUrl: '/product/2',
		categories: [],
	};
	vi.mocked( apiFetch ).mockResolvedValue( {
		items: [ product ],
		totalItems: 1,
		totalPages: 1,
		totalOutletItems: 1,
	} );
	const view = render( <ProductsHarness /> );
	await view.findByRole( 'row', { name: /Selected product/ } );

	// Act.
	fireEvent.click(
		view.getByRole( 'checkbox', { name: 'Show outlet products only' } )
	);

	// Assert.
	await waitFor( () =>
		expect( apiFetch ).toHaveBeenCalledWith(
			expect.objectContaining( {
				path: expect.stringContaining( 'membership=outlet' ),
			} )
		)
	);
	const refreshed = await view.findByRole( 'row', {
		name: /Selected product/,
	} );
	expect(
		within( refreshed ).getByRole( 'checkbox', {
			name: 'Include Selected product in outlet',
		} )
	).toBeChecked();
	expect(
		view.getByRole( 'checkbox', { name: 'Show outlet products only' } )
	).toBeChecked();
	fireEvent.click(
		view.getByRole( 'checkbox', { name: 'Show outlet products only' } )
	);
	await waitFor( () =>
		expect( apiFetch ).toHaveBeenLastCalledWith(
			expect.objectContaining( {
				path: '/outletpro/v1/products?membership=all&page=1&per_page=25&search=&order=asc',
			} )
		)
	);
	expect(
		view.getByRole( 'checkbox', { name: 'Show outlet products only' } )
	).not.toBeChecked();
} );

test( 'fetches search results from the server', async () => {
	// Arrange.
	vi.mocked( apiFetch ).mockResolvedValue( {
		items: [],
		totalItems: 0,
		totalPages: 0,
		totalOutletItems: 12,
	} );
	const view = render( <ProductsHarness /> );

	// Act.
	fireEvent.change( view.getByRole( 'searchbox' ), {
		target: { value: 'SKU-123' },
	} );

	// Assert.
	await waitFor( () =>
		expect( apiFetch ).toHaveBeenCalledWith(
			expect.objectContaining( {
				path: expect.stringContaining( 'search=SKU-123' ),
			} )
		)
	);
	expect(
		view.getByText( 'Select the products to display on the outlet page.' )
	).toBeVisible();
	expect( await view.findByText( 'No products found' ) ).toBeVisible();
} );

test( 'shows failed queries and retries at the API boundary', async () => {
	// Arrange.
	const log = vi.spyOn( console, 'error' ).mockImplementation( () => {} );
	vi.mocked( apiFetch )
		.mockRejectedValueOnce( { message: 'The network is unavailable.' } )
		.mockResolvedValue( {
			items: [],
			totalItems: 0,
			totalPages: 0,
			totalOutletItems: 0,
		} );
	const view = render( <ProductsHarness /> );
	await within( view.container ).findByText( 'The network is unavailable.' );

	// Act.
	fireEvent.click( view.getByRole( 'button', { name: 'Retry' } ) );

	// Assert.
	await waitFor( () =>
		expect(
			within( view.container ).queryByText(
				'The network is unavailable.'
			)
		).not.toBeInTheDocument()
	);
	expect( log ).toHaveBeenCalledWith(
		'Outlet products could not be loaded.',
		expect.anything()
	);
	log.mockRestore();
} );

test( 'collapses the manager without losing its filter or search', async () => {
	// Arrange.
	vi.mocked( apiFetch ).mockResolvedValue( {
		items: [],
		totalItems: 0,
		totalPages: 0,
		totalOutletItems: 0,
	} );
	const view = render( <ProductsHarness /> );
	const toggle = view.getByRole( 'button', {
		name: 'Manage outlet products',
	} );
	fireEvent.click(
		view.getByRole( 'checkbox', { name: 'Show outlet products only' } )
	);
	fireEvent.change( view.getByRole( 'searchbox' ), {
		target: { value: 'SKU-123' },
	} );
	await waitFor( () =>
		expect( apiFetch ).toHaveBeenCalledWith(
			expect.objectContaining( {
				path: expect.stringContaining( 'search=SKU-123' ),
			} )
		)
	);

	// Act.
	fireEvent.click( toggle );

	// Assert.
	expect( toggle ).toHaveAttribute( 'aria-expanded', 'false' );
	expect( view.queryByRole( 'searchbox' ) ).not.toBeInTheDocument();
	fireEvent.click( toggle );
	expect( toggle ).toHaveAttribute( 'aria-expanded', 'true' );
	expect( view.getByRole( 'searchbox' ) ).toHaveValue( 'SKU-123' );
	expect(
		view.getByRole( 'checkbox', { name: 'Show outlet products only' } )
	).toBeChecked();
} );

test.each( [
	{ age: 0, expected: '0' },
	{ age: 1234, expected: '1,234' },
	{ age: null, expected: '—' },
] )( 'renders product age $age as $expected', async ( { age, expected } ) => {
	// Arrange.
	const product: Product = {
		id: 1,
		name: 'Age product',
		sku: '',
		image: '/product.jpg',
		price: '',
		stock_status: 'instock',
		manage_stock: false,
		stock_quantity: null,
		total_sales: 0,
		age_days: age,
		status: 'publish',
		outlet: false,
		canEdit: true,
		editUrl: null,
		categories: [],
	};
	vi.mocked( apiFetch ).mockResolvedValue( {
		items: [ product ],
		totalItems: 1,
		totalPages: 1,
		totalOutletItems: 0,
	} );

	// Act.
	const view = render( <ProductsHarness /> );
	const row = await view.findByRole( 'row', { name: /Age product/ } );

	// Assert.
	expect( within( row ).getAllByRole( 'cell' )[ 4 ] ).toHaveTextContent(
		expected
	);
} );
