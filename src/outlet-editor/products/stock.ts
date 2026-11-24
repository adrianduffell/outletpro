/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { __, sprintf } from '@wordpress/i18n';
import type { Product } from './types';

export function getStockLabel( {
	stock_status: stockStatus,
	manage_stock: manageStock,
	stock_quantity: stockQuantity,
}: Pick<
	Product,
	'stock_status' | 'manage_stock' | 'stock_quantity'
> ): string {
	const labels: Record< string, string > = {
		instock: __( 'In stock', 'outletpro' ),
		outofstock: __( 'Out of stock', 'outletpro' ),
		onbackorder: __( 'On backorder', 'outletpro' ),
	};
	const label = labels[ stockStatus ] ?? stockStatus;
	if ( stockStatus !== 'instock' ) {
		return label;
	}
	if ( ! manageStock ) {
		return label;
	}
	if ( stockQuantity === null ) {
		return label;
	}
	return sprintf(
		// translators: %s: available stock quantity.
		__( 'In stock (%s)', 'outletpro' ),
		stockQuantity.toString()
	);
}
