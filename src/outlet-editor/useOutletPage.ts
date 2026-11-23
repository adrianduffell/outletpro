/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { getErrorMessage } from './error-message';
import type { PageRecord } from './types';

export default function useOutletPage( pageId: number ) {
	const [ page, setPage ] = useState< PageRecord | null >( null );
	const [ error, setError ] = useState( '' );
	const [ title, setTitle ] = useState( '' );
	const [ slug, setSlug ] = useState( '' );
	useEffect( () => {
		const controller = new AbortController();
		setPage( null );
		setError( '' );
		apiFetch< PageRecord >( {
			path: `/wp/v2/pages/${ pageId }?context=edit`,
			signal: controller.signal,
		} )
			.then( ( record ) => {
				if ( controller.signal.aborted ) {
					return;
				}
				setPage( record );
				setTitle( record.title.raw );
				setSlug( record.slug );
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
	}, [ pageId ] );
	async function save( status?: 'publish' | 'draft' ) {
		if ( page === null ) {
			throw new Error( 'Outlet page details are unavailable.' );
		}
		const record = await apiFetch< PageRecord >( {
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
		setPage( record );
		setTitle( record.title.raw );
		setSlug( record.slug );
	}
	const dirty =
		page !== null && ( title !== page.title.raw || slug !== page.slug );
	return { page, error, title, slug, setTitle, setSlug, dirty, save };
}
