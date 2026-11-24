/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

export type ProductQuery = {
	page: number;
	per_page: number;
	search_name_or_sku: string;
	orderby: 'title';
	order: 'asc' | 'desc';
	category?: string;
	outletpro?: true;
};

export const PRODUCT_FIELDS = [
	'id',
	'name',
	'sku',
	'stock_status',
	'manage_stock',
	'stock_quantity',
	'total_sales',
	'status',
	'categories',
	'outletpro',
	'images',
	'price_html',
	'date_created_gmt',
	'type',
	'_links',
].join( ',' );

export const PRODUCT_ENTITY = {
	kind: 'outletpro',
	name: 'product',
	baseURL: '/wc/v3/products',
	baseURLParams: { context: 'edit', _fields: PRODUCT_FIELDS },
	supportsPagination: true,
};
