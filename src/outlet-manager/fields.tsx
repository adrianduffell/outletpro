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
import type { ManagerConfig, Product } from './types';

export function getProductFields(
	categories: ManagerConfig[ 'categories' ]
): Field< Product >[] {
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
			render: ( { item } ) => (
				<img
					className="outletpro-manager-product-image"
					src={ item.image }
					width={ 48 }
					height={ 48 }
					alt=""
					loading="lazy"
				/>
			),
		},
		{
			id: 'name',
			label: __( 'Product', 'outletpro' ),
			enableHiding: false,
			filterBy: false,
			render: ( { item } ) =>
				item.editUrl ? (
					<a
						className="outletpro-manager-product-name"
						href={ item.editUrl }
					>
						{ decodeEntities( item.name ) }
					</a>
				) : (
					<span className="outletpro-manager-product-name">
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
				<RawHTML className="outletpro-manager-price">
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
				<span className="outletpro-manager-outlet-heading">
					{ __( 'Outlet', 'outletpro' ) }
				</span>
			),
			enableHiding: false,
			enableSorting: false,
			filterBy: false,
			render: OutletToggle,
		},
		{
			id: 'category',
			label: __( 'Category', 'outletpro' ),
			enableSorting: false,
			elements: categories.map( ( category ) => ( {
				...category,
				label: decodeEntities( category.label ),
			} ) ),
			filterBy: { operators: [ 'is' ], isPrimary: true },
			getValue: ( { item } ) =>
				item.categories
					.map( ( category ) => category.name )
					.join( ', ' ),
		},
	];
}
