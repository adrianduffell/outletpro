/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { fireEvent, render, within, waitFor } from '@testing-library/react';
import apiFetch from '@wordpress/api-fetch';
import { expect, test, vi } from 'vitest';
import OutletEditorScreen from '../../OutletEditorScreen';
import { createEditorRegistry } from '../../test-utils';
import { registerProductEntity } from '../../../data/products';
import {
	createProduct,
	productsResponse,
} from '../../../data/products/test-utils/store-fixtures';

vi.mock( '@wordpress/api-fetch', () => ( { default: vi.fn() } ) );
vi.hoisted( () => {
	vi.stubGlobal( 'ResizeObserver', function ResizeObserver() {
		return { observe() {}, unobserve() {}, disconnect() {} };
	} );
} );

test( 'includes product edits in native review and saves only selected products', async () => {
	// Arrange.
	vi.stubGlobal(
		'matchMedia',
		vi.fn( () => ( {
			matches: true,
			addListener: vi.fn(),
			removeListener: vi.fn(),
		} ) )
	);
	const page = {
		id: 39,
		title: { raw: 'Outlet' },
		slug: 'outlet',
		status: 'draft',
		link: '/outlet/',
	};
	let products = [
		createProduct(),
		createProduct( { id: 2, name: 'Scarf &amp; hat' } ),
	];
	const writes: { path: string; data: unknown }[] = [];
	vi.mocked( apiFetch ).mockReset();
	vi.mocked( apiFetch ).mockImplementation( async ( options ) => {
		const path = new URL( options.path ?? '', 'http://localhost' ).pathname;
		if ( path === '/wp/v2/product' ) {
			return new Response( null, { headers: { Allow: 'GET, POST' } } );
		}
		if ( [ 'POST', 'PUT' ].includes( options.method ?? '' ) ) {
			writes.push( { path, data: options.data } );
			if ( path === '/wp/v2/pages/39' ) {
				return { ...page, ...options.data };
			}
			products = products.map( ( product ) =>
				path === `/wc/v3/products/${ product.id }`
					? { ...product, ...options.data }
					: product
			);
			return products.find( ( product ) =>
				path.endsWith( `/${ product.id }` )
			);
		}
		if ( path.startsWith( '/wc/v3/products' ) ) {
			return productsResponse( products );
		}
		return new Response( JSON.stringify( page ) );
	} );
	const { registry, wrapper } = createEditorRegistry( {
		outletpro_markdown_levels: [],
	} );
	registerProductEntity( registry );
	const view = render(
		<OutletEditorScreen config={ { pageId: 39, canPublish: true } } />,
		{ wrapper }
	);
	const hat = await view.findByRole( 'checkbox', {
		name: 'Include Hat in outlet',
	} );
	const scarf = view.getByRole( 'checkbox', {
		name: 'Include Scarf & hat in outlet',
	} );
	const slug = await view.findByRole( 'textbox', { name: 'Slug' } );
	const save = within(
		view.getByRole( 'toolbar', { name: 'Page actions' } )
	).getByRole( 'button', { name: 'Save' } );

	// Act.
	fireEvent.click( hat );
	fireEvent.click( scarf );
	fireEvent.change( slug, { target: { value: 'sale' } } );
	fireEvent.click( save );
	const review = within(
		view.getByRole( 'dialog', { name: 'Are you ready to save?' } )
	);
	expect( review.getByRole( 'button', { name: 'Products' } ) ).toBeVisible();
	fireEvent.click( review.getByRole( 'checkbox', { name: 'Scarf & hat' } ) );
	fireEvent.click( review.getByRole( 'checkbox', { name: 'Outlet' } ) );
	fireEvent.click( review.getByRole( 'button', { name: 'Save' } ) );
	await within( view.container ).findByText( 'Site updated.' );

	// Assert.
	expect( writes ).toEqual( [
		{
			path: '/wc/v3/products/1',
			data: { id: 1, outletpro: { is_outlet: true } },
		},
	] );
	expect( slug ).toHaveValue( 'sale' );
	expect( scarf ).toBeChecked();
	expect( save ).toBeEnabled();
	fireEvent.click( save );
	const remaining = within(
		view.getByRole( 'dialog', { name: 'Are you ready to save?' } )
	);
	expect(
		remaining.getByRole( 'checkbox', { name: 'Scarf & hat' } )
	).toBeChecked();
	expect(
		remaining.queryByRole( 'checkbox', { name: 'Hat' } )
	).not.toBeInTheDocument();
	fireEvent.click( remaining.getByRole( 'button', { name: 'Save' } ) );
	await waitFor( () => expect( save ).toBeDisabled() );
	expect( writes.slice( 1 ) ).toEqual(
		expect.arrayContaining( [
			{
				path: '/wc/v3/products/2',
				data: { id: 2, outletpro: { is_outlet: true } },
			},
			{ path: '/wp/v2/pages/39', data: { id: 39, slug: 'sale' } },
		] )
	);
} );
