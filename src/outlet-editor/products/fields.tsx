/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import type { Field } from '@wordpress/dataviews/wp';
import { decodeEntities } from '@wordpress/html-entities';
import { __ } from '@wordpress/i18n';
import type { RestProduct } from '../../data/products/types';

export function getProductFields(): Field< RestProduct >[] {
	return [
		{
			id: 'name',
			label: __( 'Product', 'outletpro' ),
			enableHiding: false,
			filterBy: false,
			render: ( { item } ) => decodeEntities( item.name ),
		},
		{
			id: 'sku',
			label: __( 'SKU', 'outletpro' ),
			enableSorting: false,
			filterBy: false,
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
			render: ( { item } ) => (
				<div className="outletpro-editor-outlet-cell">
					{ item.outletpro.is_outlet
						? __( 'Yes', 'outletpro' )
						: __( 'No', 'outletpro' ) }
				</div>
			),
		},
	];
}
