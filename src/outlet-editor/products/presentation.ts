/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import type { Product } from './types';
import type { RestProduct } from '../../data/products/types';

export function getProductRow(
	product: RestProduct,
	canEdit: boolean,
	now = Date.now()
): Product {
	if ( typeof product.outletpro?.is_outlet !== 'boolean' ) {
		throw new Error( 'Outlet product membership is unavailable.' );
	}
	const created =
		product.date_created_gmt === null
			? NaN
			: Date.parse( product.date_created_gmt + 'Z' );
	const age = Number.isFinite( created )
		? Math.max( 0, Math.floor( ( now - created ) / 86400000 ) )
		: null;
	const editable =
		canEdit && product.status !== 'trash' && product.type !== 'variation';
	return {
		...product,
		canEdit: editable,
		image:
			product.images[ 0 ]?.thumbnail || product.images[ 0 ]?.src || null,
		price: sanitizePrice( product.price_html ),
		age_days: age,
	};
}

export function sanitizePrice( html: string ): string {
	const template = document.createElement( 'template' );
	template.innerHTML = html;
	for ( const node of template.content.querySelectorAll( '*' ) ) {
		if ( [ 'SCRIPT', 'STYLE' ].includes( node.tagName ) ) {
			node.remove();
			continue;
		}
		if (
			! [ 'SPAN', 'BDI', 'DEL', 'INS', 'SMALL', 'ABBR', 'BR' ].includes(
				node.tagName
			)
		) {
			node.replaceWith( ...node.childNodes );
			continue;
		}
		for ( const attribute of Array.from( node.attributes ) ) {
			if (
				[ 'class', 'aria-hidden', 'aria-label' ].includes(
					attribute.name
				)
			) {
				continue;
			}
			node.removeAttribute( attribute.name );
		}
	}
	return template.innerHTML;
}
