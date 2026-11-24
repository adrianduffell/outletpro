/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import {
	store as coreStore,
	useEntityProp,
	useEntityRecord,
} from '@wordpress/core-data';
import { useDispatch, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { getErrorMessage } from './error-message';
import type { PageRecord } from './types';

export default function useOutletPage( pageId: number ) {
	const {
		record: page,
		hasEdits: dirty,
		save: saveEdits,
	} = useEntityRecord< PageRecord >( 'postType', 'page', pageId );
	const [ title = '', updateTitle ] = useEntityProp(
		'postType',
		'page',
		'title',
		pageId
	);
	const [ slug = '', updateSlug ] = useEntityProp(
		'postType',
		'page',
		'slug',
		pageId
	);
	const { editEntityRecord } = useDispatch( coreStore );
	const failure = useSelect(
		( select ) =>
			select( coreStore ).getResolutionError( 'getEntityRecord', [
				'postType',
				'page',
				pageId,
			] ),
		[ pageId ]
	);
	const error = failure
		? getErrorMessage(
				failure,
				__( 'Page details could not be loaded.', 'outletpro' )
			)
		: '';
	function setTitle( value: string ) {
		updateTitle( value );
	}
	function setSlug( value: string ) {
		updateSlug( value );
	}
	async function save( status?: 'publish' | 'draft' ) {
		if ( page === null ) {
			throw new Error( 'Outlet page details are unavailable.' );
		}
		if ( status !== undefined ) {
			editEntityRecord( 'postType', 'page', pageId, {
				status,
				...( status === 'publish'
					? { date: new Date().toISOString() }
					: {} ),
			} );
		}
		await saveEdits();
	}
	return { page, error, title, slug, setTitle, setSlug, dirty, save };
}
