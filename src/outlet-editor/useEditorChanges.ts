/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type { ChangeSelection, ChangeSource } from './changes';
import { getErrorMessage } from './error-message';

export default function useEditorChanges( sources: ChangeSource[] ) {
	const request = useRef( { saving: false, failed: false } );
	const [ busy, setBusy ] = useState( false );
	const [ failed, setFailed ] = useState( false );
	const [ error, setError ] = useState( '' );
	const dirty = sources.some( ( source ) => source.changes.length > 0 );
	useEffect( () => {
		if ( ! dirty && ! busy ) {
			return;
		}
		function warnBeforeLeaving( event: BeforeUnloadEvent ) {
			event.preventDefault();
			event.returnValue = '';
		}
		window.addEventListener( 'beforeunload', warnBeforeLeaving );
		return () =>
			window.removeEventListener( 'beforeunload', warnBeforeLeaving );
	}, [ dirty, busy ] );
	async function save( selected: ChangeSelection ) {
		if ( request.current.failed ) {
			throw new Error(
				'Outlet editor cannot save after a failed request.'
			);
		}
		if ( request.current.saving ) {
			throw new Error( 'Outlet editor changes are already being saved.' );
		}
		request.current.saving = true;
		setBusy( true );
		try {
			for ( const source of sources ) {
				const ids = selected[ source.id ] ?? [];
				if ( ids.length === 0 ) {
					continue;
				}
				await source.save( ids );
			}
			return true;
		} catch ( failure ) {
			request.current.failed = true;
			setFailed( true );
			// eslint-disable-next-line no-console -- Log system failures at the save boundary.
			console.error(
				'Outlet editor changes could not be saved.',
				failure
			);
			setError(
				getErrorMessage(
					failure,
					__( 'Changes could not be saved.', 'outletpro' )
				)
			);
			return false;
		} finally {
			request.current.saving = false;
			setBusy( false );
		}
	}
	return { busy, failed, error, dirty, save };
}
