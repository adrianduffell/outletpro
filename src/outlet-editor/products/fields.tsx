/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import type { Field } from '@wordpress/dataviews/wp';
import { RawHTML } from '@wordpress/element';
import { decodeEntities } from '@wordpress/html-entities';
import { __ } from '@wordpress/i18n';
import OutletToggle from './OutletToggle';
import { getStockLabel } from './stock';
import type { Product } from './types';

export function getProductFields(): Field< Product >[] {
	return [
		{
			id: 'image',
			label: __( 'Image', 'outletpro' ),
			header: (
				<span className="screen-reader-text">
					{ __( 'Image', 'outletpro' ) }
				</span>
			),
			enableSorting: false,
			filterBy: false,
			render: ( { item } ) =>
				item.image ? (
					<img
						className="outletpro-editor-product-image"
						src={ item.image }
						width={ 48 }
						height={ 48 }
						alt=""
						loading="lazy"
					/>
				) : null,
		},
		{
			id: 'name',
			label: __( 'Product', 'outletpro' ),
			enableHiding: false,
			filterBy: false,
			render: ( { item } ) =>
				item.canEdit ? (
					<a
						className="outletpro-editor-product-name"
						href={ `post.php?post=${ item.id }&action=edit` }
					>
						{ decodeEntities( item.name ) }
					</a>
				) : (
					<span className="outletpro-editor-product-name">
						{ decodeEntities( item.name ) }
					</span>
				),
		},
		{
			id: 'sku',
			label: __( 'SKU', 'outletpro' ),
			enableSorting: false,
			filterBy: false,
			render: ( { item } ) => item.sku,
		},
		{
			id: 'price',
			label: __( 'Price', 'outletpro' ),
			enableSorting: false,
			filterBy: false,
			render: ( { item } ) => (
				<RawHTML className="outletpro-editor-price">
					{ item.price }
				</RawHTML>
			),
		},
		{
			id: 'stock_status',
			label: __( 'Stock', 'outletpro' ),
			enableSorting: false,
			filterBy: false,
			render: ( { item } ) => getStockLabel( item ),
		},
		{
			id: 'total_sales',
			label: __( 'Total Sales', 'outletpro' ),
			type: 'integer',
			enableSorting: false,
			filterBy: false,
		},
		{
			id: 'age_days',
			label: __( 'Age (days)', 'outletpro' ),
			type: 'integer',
			enableSorting: false,
			filterBy: false,
			render: ( { item } ) =>
				item.age_days === null ? '—' : item.age_days.toLocaleString(),
		},
		{
			id: 'outlet',
			label: __( 'Outlet', 'outletpro' ),
			header: (
				<span className="outletpro-editor-outlet-heading">
					{ __( 'Outlet', 'outletpro' ) }
				</span>
			),
			enableHiding: false,
			enableSorting: false,
			filterBy: false,
			render: OutletToggle,
		},
	];
}
