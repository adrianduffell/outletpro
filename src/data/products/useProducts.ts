/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { PRODUCT_ENTITY } from './product-entity';
import type { ProductQuery } from './product-entity';
import type { RestProduct } from './types';

export default function useProducts( query: ProductQuery ) {
	return useSelect(
		( select ) => {
			const data = select( coreStore );
			const args = [ PRODUCT_ENTITY.kind, PRODUCT_ENTITY.name, query ];
			const products = data.getEntityRecords< RestProduct >(
				PRODUCT_ENTITY.kind,
				PRODUCT_ENTITY.name,
				query
			);
			const isLoading =
				data.isResolving( 'getEntityRecords', args ) ||
				! data.hasFinishedResolution( 'getEntityRecords', args );
			return {
				records: products,
				totalItems: data.getEntityRecordsTotalItems(
					PRODUCT_ENTITY.kind,
					PRODUCT_ENTITY.name,
					query
				),
				loading: isLoading,
				// Core-data catches collection failures and leaves the result null.
				// An empty array is a successful query with no matching products.
				error: ! isLoading && products === null,
			};
		},
		[ query ]
	);
}
