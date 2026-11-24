/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { store as coreStore } from '@wordpress/core-data';
import { createRegistry, RegistryProvider } from '@wordpress/data';
import { useState } from '@wordpress/element';
import type { ReactNode } from 'react';
import type { RestProduct } from '../types';
import { registerProductEntity } from '..';
import { PRODUCT_ENTITY } from '../product-entity';

export function createProductsRegistry( products: RestProduct[] = [] ) {
	const registry = createRegistry();
	registry.register( coreStore );
	registerProductEntity( registry );
	registry
		.dispatch( coreStore )
		.receiveEntityRecords(
			PRODUCT_ENTITY.kind,
			PRODUCT_ENTITY.name,
			products
		);
	for ( const product of products ) {
		registry
			.dispatch( coreStore )
			.finishResolution( 'getEntityRecord', [
				PRODUCT_ENTITY.kind,
				PRODUCT_ENTITY.name,
				product.id,
			] );
	}
	return registry;
}

export function ProductsRegistry( { children }: { children: ReactNode } ) {
	const [ registry ] = useState( createProductsRegistry );
	return <RegistryProvider value={ registry }>{ children }</RegistryProvider>;
}

export function createProduct(
	edits: Partial< RestProduct > = {}
): RestProduct {
	return {
		id: 1,
		name: 'Hat',
		sku: 'HAT',
		type: 'simple',
		status: 'publish',
		images: [ { src: '/hat.jpg', thumbnail: '/hat-small.jpg' } ],
		price_html: '£10',
		date_created_gmt: '2026-11-01T00:00:00',
		stock_status: 'instock',
		manage_stock: false,
		stock_quantity: null,
		total_sales: 0,
		categories: [],
		outletpro: { is_outlet: false },
		_links: {
			self: [
				{
					href: '/wc/v3/products/1',
					targetHints: { allow: [ 'GET', 'PUT' ] },
				},
			],
		},
		...edits,
	};
}

export function productsResponse(
	products: RestProduct[],
	total = products.length,
	totalPages = Math.ceil( total / 25 )
) {
	return new Response( JSON.stringify( products ), {
		headers: {
			'X-WP-Total': total.toString(),
			'X-WP-TotalPages': totalPages.toString(),
		},
	} );
}
