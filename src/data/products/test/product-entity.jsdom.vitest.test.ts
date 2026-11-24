/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import apiFetch from '@wordpress/api-fetch';
import { store as coreStore } from '@wordpress/core-data';
import { expect, test, vi } from 'vitest';
import { PRODUCT_ENTITY, PRODUCT_FIELDS } from '../product-entity';
import type { ProductQuery } from '../product-entity';
import {
	createProduct,
	createProductsRegistry,
	productsResponse,
} from '../test-utils/store-fixtures';
import type { RestProduct } from '../types';

vi.mock( '@wordpress/api-fetch', () => ( { default: vi.fn() } ) );

test( 'caches product queries and reads pagination totals from REST headers', async () => {
	// Arrange.
	const registry = createProductsRegistry();
	const query: ProductQuery = {
		page: 1,
		per_page: 25,
		search_name_or_sku: '',
		orderby: 'title',
		order: 'asc',
	};
	const product = createProduct();
	const products = Array.from( { length: 25 }, ( _, index ) =>
		createProduct( { id: index + 1 } )
	);
	vi.mocked( apiFetch ).mockReset();
	vi.mocked( apiFetch ).mockImplementation( async () =>
		productsResponse( products, 51, 3 )
	);
	const { kind, name } = PRODUCT_ENTITY;

	// Act.
	const records = await registry
		.resolveSelect( coreStore )
		.getEntityRecords< RestProduct >( kind, name, query );
	await registry
		.resolveSelect( coreStore )
		.getEntityRecords< RestProduct >( kind, name, { ...query } );
	const record = await registry
		.resolveSelect( coreStore )
		.getEntityRecord< RestProduct >( kind, name, product.id );

	// Assert.
	expect( records ).toEqual( products );
	expect( record ).toEqual( product );
	expect(
		registry
			.select( coreStore )
			.getEntityRecordsTotalItems( kind, name, query )
	).toBe( 51 );
	expect(
		registry
			.select( coreStore )
			.getEntityRecordsTotalPages( kind, name, query )
	).toBe( 3 );
	expect( apiFetch ).toHaveBeenCalledTimes( 1 );
	const path = new URL(
		vi.mocked( apiFetch ).mock.calls[ 0 ][ 0 ].path ?? '',
		'http://localhost'
	);
	expect( path.pathname ).toBe( '/wc/v3/products' );
	expect( path.searchParams.get( '_fields' ) ).toBe( PRODUCT_FIELDS );
	expect( path.searchParams.get( 'context' ) ).toBe( 'edit' );
} );

test( 'caches each product page independently', async () => {
	// Arrange.
	const registry = createProductsRegistry();
	const first = createProduct();
	const second = createProduct( { id: 2, name: 'Scarf' } );
	const query: ProductQuery = {
		page: 1,
		per_page: 1,
		search_name_or_sku: '',
		orderby: 'title',
		order: 'asc',
	};
	vi.mocked( apiFetch ).mockReset();
	vi.mocked( apiFetch ).mockImplementation( async ( options ) => {
		const page = new URL(
			options.path ?? '',
			'http://localhost'
		).searchParams.get( 'page' );
		return productsResponse( [ page === '2' ? second : first ], 2, 2 );
	} );
	const { kind, name } = PRODUCT_ENTITY;

	// Act.
	const firstPage = await registry
		.resolveSelect( coreStore )
		.getEntityRecords< RestProduct >( kind, name, query );
	const secondPage = await registry
		.resolveSelect( coreStore )
		.getEntityRecords< RestProduct >( kind, name, { ...query, page: 2 } );
	const cachedFirstPage = await registry
		.resolveSelect( coreStore )
		.getEntityRecords< RestProduct >( kind, name, query );

	// Assert.
	expect( firstPage ).toEqual( [ first ] );
	expect( secondPage ).toEqual( [ second ] );
	expect( cachedFirstPage ).toEqual( firstPage );
	expect( apiFetch ).toHaveBeenCalledTimes( 2 );
} );

test( 'refreshes filtered products and totals after membership changes', async () => {
	// Arrange.
	const registry = createProductsRegistry();
	const { kind, name } = PRODUCT_ENTITY;
	const all: ProductQuery = {
		page: 1,
		per_page: 25,
		search_name_or_sku: '',
		orderby: 'title',
		order: 'asc',
	};
	const outletOnly: ProductQuery = { ...all, outletpro: true };
	let membership = true;
	vi.mocked( apiFetch ).mockReset();
	vi.mocked( apiFetch ).mockImplementation( async ( options ) => {
		if ( options.method === 'PUT' ) {
			membership = false;
			return createProduct( { outletpro: { is_outlet: membership } } );
		}
		const outlet = new URL(
			options.path ?? '',
			'http://localhost'
		).searchParams.get( 'outletpro' );
		if ( outlet === 'true' && ! membership ) {
			return productsResponse( [] );
		}
		return productsResponse( [
			createProduct( { outletpro: { is_outlet: membership } } ),
		] );
	} );
	await registry
		.resolveSelect( coreStore )
		.getEntityRecords< RestProduct >( kind, name, all );
	await registry
		.resolveSelect( coreStore )
		.getEntityRecords< RestProduct >( kind, name, outletOnly );

	// Act.
	await registry
		.dispatch( coreStore )
		.saveEntityRecord(
			kind,
			name,
			{ id: 1, outletpro: { is_outlet: false } },
			{ throwOnError: true }
		);
	const refreshedAll = await registry
		.resolveSelect( coreStore )
		.getEntityRecords< RestProduct >( kind, name, all );
	const refreshedOutlet = await registry
		.resolveSelect( coreStore )
		.getEntityRecords< RestProduct >( kind, name, outletOnly );

	// Assert.
	expect( refreshedAll ).toEqual( [
		createProduct( { outletpro: { is_outlet: false } } ),
	] );
	expect( refreshedOutlet ).toEqual( [] );
	expect(
		registry
			.select( coreStore )
			.getEntityRecordsTotalItems( kind, name, all )
	).toBe( 1 );
	expect(
		registry
			.select( coreStore )
			.getEntityRecordsTotalItems( kind, name, outletOnly )
	).toBe( 0 );
	expect( apiFetch ).toHaveBeenCalledTimes( 5 );
} );
