/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import {
	store as coreStore,
	useEntityProp,
	useEntityRecord,
} from '@wordpress/core-data';
import { useDispatch, useRegistry, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { getErrorMessage } from './error-message';
import type { EditablePageStatus, PageRecord } from './types';

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
	const [ status = '' ] = useEntityProp(
		'postType',
		'page',
		'status',
		pageId
	);
	const { editEntityRecord } = useDispatch( coreStore );
	const registry = useRegistry();
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
	function setStatus( value: EditablePageStatus ) {
		// Discard a publication date prepared for an earlier failed save.
		editEntityRecord( 'postType', 'page', pageId, {
			status: value,
			date: undefined,
		} );
	}
	function prepareSave( nextStatus?: EditablePageStatus ) {
		if ( page === null ) {
			throw new Error( 'Outlet page details are unavailable.' );
		}
		const edits = registry
			.select( coreStore )
			.getEntityRecordEdits( 'postType', 'page', pageId );
		const previous = { status: edits?.status, date: edits?.date };
		if ( nextStatus !== undefined ) {
			setStatus( nextStatus );
		}
		if (
			( nextStatus ?? edits?.status ?? page.status ) === 'publish' &&
			page.status !== 'publish'
		) {
			editEntityRecord( 'postType', 'page', pageId, {
				date: new Date().toISOString(),
			} );
		}
		return previous;
	}
	async function save( nextStatus?: EditablePageStatus ) {
		prepareSave( nextStatus );
		await saveEdits();
	}
	return {
		page,
		error,
		title,
		slug,
		status,
		setTitle,
		setSlug,
		setStatus,
		prepareSave,
		dirty,
		save,
	};
}
