/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import type { View } from '@wordpress/dataviews/wp';

export const INITIAL_VIEW: View = {
	type: 'table',
	page: 1,
	perPage: 25,
	search: '',
	fields: [
		'image',
		'name',
		'sku',
		'total_sales',
		'age_days',
		'price',
		'stock_status',
		'outlet',
	],
	layout: {
		styles: {
			image: { width: 48, minWidth: 48, maxWidth: 48, align: 'center' },
			name: { align: 'start' },
			sku: { align: 'center' },
			total_sales: { align: 'center' },
			age_days: { align: 'center' },
			price: { align: 'center' },
			stock_status: { align: 'center' },
			outlet: { align: 'center' },
		},
	},
	filters: [],
	sort: { field: 'name', direction: 'asc' },
};

export function getProductsPath( view: View, membership: string ): string {
	const params = new URLSearchParams( {
		membership,
		page: ( view.page ?? 1 ).toString(),
		per_page: ( view.perPage ?? 25 ).toString(),
		search: view.search ?? '',
		order: view.sort?.direction ?? 'asc',
	} );
	const category = view.filters?.find(
		( filter ) => filter.field === 'category'
	);
	if ( category !== undefined ) {
		if ( ! Number.isInteger( category.value ) ) {
			throw new Error( 'Invalid product category filter.' );
		}
		if ( category.value < 1 ) {
			throw new Error( 'Invalid product category filter.' );
		}
		params.set( 'category', category.value.toString() );
	}
	return `/outletpro/v1/products?${ params }`;
}

export function getNextView( previous: View, next: View ): View {
	const queryChanged =
		previous.search !== next.search ||
		previous.perPage !== next.perPage ||
		JSON.stringify( previous.sort ) !== JSON.stringify( next.sort ) ||
		JSON.stringify( previous.filters ) !== JSON.stringify( next.filters );
	return queryChanged ? { ...next, page: 1 } : next;
}

export function getErrorMessage( error: unknown, fallback: string ): string {
	if ( typeof error !== 'object' ) {
		return fallback;
	}
	if ( error === null ) {
		return fallback;
	}
	if ( 'message' in error && typeof error.message === 'string' ) {
		return error.message;
	}
	return fallback;
}
