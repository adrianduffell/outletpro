/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { FormToggle } from '@wordpress/components';
import { createContext, memo, useContext } from '@wordpress/element';
import { decodeEntities } from '@wordpress/html-entities';
import { __, sprintf } from '@wordpress/i18n';
import type { Product } from './types';

export const OutletControlsContext = createContext<
	( ( products: Product[], outlet: boolean ) => void ) | null
>( null );

export default memo(
	function OutletToggle( { item }: { item: Product } ) {
		const updateProducts = useContext( OutletControlsContext );
		if ( updateProducts === null ) {
			throw new Error( 'Outlet controls are unavailable.' );
		}
		return (
			<div className="outletpro-manager-outlet-cell">
				<FormToggle
					aria-label={ sprintf(
						// translators: %s: product name.
						__( 'Include %s in outlet', 'outletpro' ),
						decodeEntities( item.name )
					) }
					checked={ item.outlet }
					disabled={ ! item.canEdit }
					onChange={ ( event ) =>
						updateProducts( [ item ], event.target.checked )
					}
				/>
			</div>
		);
	},
	( previous, next ) =>
		previous.item.id === next.item.id &&
		previous.item.name === next.item.name &&
		previous.item.outlet === next.item.outlet &&
		previous.item.canEdit === next.item.canEdit
);
