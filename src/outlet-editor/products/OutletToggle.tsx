/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { FormToggle } from '@wordpress/components';
import { store as coreStore, useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { decodeEntities } from '@wordpress/html-entities';
import { __, sprintf } from '@wordpress/i18n';
import { PRODUCT_ENTITY } from '../../data/products/product-entity';
import type { Product } from './types';

export default function OutletToggle( { item }: { item: Product } ) {
	const { kind, name } = PRODUCT_ENTITY;
	const [ outletpro, setOutletpro ] = useEntityProp(
		kind,
		name,
		'outletpro',
		item.id
	);
	const saving = useSelect(
		( select ) =>
			select( coreStore ).isSavingEntityRecord( kind, name, item.id ),
		[ kind, name, item.id ]
	);
	return (
		<div className="outletpro-editor-outlet-cell">
			<FormToggle
				aria-label={ sprintf(
					/* translators: %s: product name. */
					__( 'Include %s in outlet', 'outletpro' ),
					decodeEntities( item.name )
				) }
				checked={ outletpro?.is_outlet === true }
				disabled={ ! item.canEdit || saving }
				onChange={ ( event ) =>
					setOutletpro( {
						...outletpro,
						is_outlet: event.target.checked,
					} )
				}
			/>
		</div>
	);
}
