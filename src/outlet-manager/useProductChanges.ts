/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import apiFetch from '@wordpress/api-fetch';
import { useCallback, useRef, useState } from '@wordpress/element';
import { decodeEntities } from '@wordpress/html-entities';
import { __, sprintf } from '@wordpress/i18n';
import { getErrorMessage } from './query';
import type { BatchResponse, Product } from './types';

export type ProductChange = Pick< Product, 'id' | 'name' | 'outlet' > & {
	original: boolean;
};

export default function useProductChanges() {
	const [ changes, setChanges ] = useState< ProductChange[] >( [] );
	const [ revision, setRevision ] = useState( 0 );
	const [ committed, setCommitted ] = useState< ProductChange[] >( [] );
	const saving = useRef( false );

	const stage = useCallback(
		(
			products: Pick< Product, 'id' | 'name' | 'outlet' | 'canEdit' >[],
			outlet: boolean
		) => {
			if ( saving.current ) {
				return;
			}
			setChanges( ( current ) => {
				const next = new Map(
					current.map( ( change ) => [ change.id, change ] )
				);
				for ( const product of products ) {
					if ( ! product.canEdit ) {
						continue;
					}
					const original =
						next.get( product.id )?.original ?? product.outlet;
					if ( original === outlet ) {
						next.delete( product.id );
						continue;
					}
					next.set( product.id, {
						id: product.id,
						name: product.name,
						original,
						outlet,
					} );
				}
				return [ ...next.values() ];
			} );
		},
		[]
	);

	async function save(
		onError: ( message: string ) => void,
		ids?: number[]
	) {
		if ( saving.current ) {
			return false;
		}
		saving.current = true;
		let success = true;
		const saved = new Set< number >();
		try {
			for ( const outlet of [ true, false ] ) {
				const group = changes.filter(
					( change ) =>
						change.outlet === outlet &&
						( ids === undefined || ids.includes( change.id ) )
				);
				for ( let offset = 0; offset < group.length; offset += 100 ) {
					const batch = group.slice( offset, offset + 100 );
					try {
						const response = await apiFetch< BatchResponse >( {
							path: '/outletpro/v1/products/batch',
							method: 'POST',
							data: {
								ids: batch.map( ( change ) => change.id ),
								outlet,
							},
						} );
						for ( const change of batch ) {
							const result = response.results.find(
								( item ) => item.id === change.id
							);
							if ( result?.outlet === outlet && ! result.error ) {
								saved.add( change.id );
								continue;
							}
							success = false;
							onError(
								sprintf(
									// translators: %1$s: product name, %2$s: failure reason.
									__( '%1$s: %2$s', 'outletpro' ),
									decodeEntities( change.name ),
									result?.error ??
										__(
											'Product could not be saved. Try again.',
											'outletpro'
										)
								)
							);
						}
					} catch ( failure ) {
						// eslint-disable-next-line no-console -- Log system failures at the API boundary.
						console.error(
							'Outlet membership could not be updated.',
							failure
						);
						success = false;
						onError(
							getErrorMessage(
								failure,
								__(
									'Products could not be saved. Try again.',
									'outletpro'
								)
							)
						);
					}
				}
			}
		} finally {
			saving.current = false;
			setCommitted(
				changes.filter( ( change ) => saved.has( change.id ) )
			);
			setChanges( ( current ) =>
				current.filter( ( change ) => ! saved.has( change.id ) )
			);
			setRevision( ( current ) => current + 1 );
		}
		return success;
	}

	return { changes, stage, save, revision, committed };
}
