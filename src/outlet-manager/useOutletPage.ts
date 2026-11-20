/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import apiFetch from '@wordpress/api-fetch';
import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { getErrorMessage } from './query';
import type { PageRecord } from './types';

export default function useOutletPage( pageId: number, productsDirty = false ) {
	const [ page, setPage ] = useState< PageRecord | null >( null );
	const [ title, setTitle ] = useState( '' );
	const [ slug, setSlug ] = useState( '' );
	const [ error, setError ] = useState( '' );
	const [ saved, setSaved ] = useState( false );
	const [ busy, setBusy ] = useState( false );
	const [ revision, setRevision ] = useState( 0 );
	const saving = useRef( false );
	const dirty =
		page !== null && ( title !== page.title.raw || slug !== page.slug );

	useEffect( () => {
		const controller = new AbortController();
		setError( '' );
		apiFetch< PageRecord >( {
			path: `/wp/v2/pages/${ pageId }?context=edit`,
			signal: controller.signal,
		} )
			.then( ( result ) => {
				if ( controller.signal.aborted ) {
					return;
				}
				setPage( result );
				setTitle( result.title.raw );
				setSlug( result.slug );
			} )
			.catch( ( failure: unknown ) => {
				if ( controller.signal.aborted ) {
					return;
				}
				// eslint-disable-next-line no-console -- Log system failures at the API boundary.
				console.error(
					'Outlet page details could not be retrieved.',
					failure
				);
				setError(
					getErrorMessage(
						failure,
						__( 'Page details could not be loaded.', 'outletpro' )
					)
				);
			} );
		return () => controller.abort();
	}, [ pageId, revision ] );

	useEffect( () => {
		if ( ! dirty && ! productsDirty ) {
			return;
		}
		function warnBeforeLeaving( event: BeforeUnloadEvent ) {
			event.preventDefault();
			event.returnValue = '';
		}
		window.addEventListener( 'beforeunload', warnBeforeLeaving );
		return () =>
			window.removeEventListener( 'beforeunload', warnBeforeLeaving );
	}, [ dirty, productsDirty ] );

	async function save(
		status?: 'publish' | 'draft',
		onError?: ( message: string ) => void
	) {
		if ( saving.current ) {
			return;
		}
		saving.current = true;
		setBusy( true );
		setSaved( false );
		setError( '' );
		try {
			const result = await apiFetch< PageRecord >( {
				path: `/wp/v2/pages/${ pageId }`,
				method: 'POST',
				data: {
					title,
					slug,
					...( status === undefined ? {} : { status } ),
					...( status === 'publish'
						? { date: new Date().toISOString() }
						: {} ),
				},
			} );
			setPage( result );
			setTitle( result.title.raw );
			setSlug( result.slug );
			setSaved( true );
			return true;
		} catch ( failure ) {
			// eslint-disable-next-line no-console -- Log system failures at the API boundary.
			console.error( 'Outlet page details could not be saved.', failure );
			const message = getErrorMessage(
				failure,
				__( 'Page details could not be saved.', 'outletpro' )
			);
			setError( message );
			onError?.( message );
			return false;
		} finally {
			saving.current = false;
			setBusy( false );
		}
	}

	return {
		page,
		title,
		slug,
		error,
		saved,
		busy,
		dirty,
		setTitle,
		setSlug,
		setSaved,
		save,
		retry: () => setRevision( ( current ) => current + 1 ),
	};
}
