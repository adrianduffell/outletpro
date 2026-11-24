/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import type { MouseEvent, ReactNode } from 'react';
import { Button, Notice } from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { useDispatch, useRegistry, useSelect } from '@wordpress/data';
import {
	EntitiesSavedStates,
	UnsavedChangesWarning,
	useEntitiesSavedStatesIsDirty,
} from '@wordpress/editor';
import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { InlineNotices, SnackbarNotices } from '@wordpress/notices';
import EditorFrame from './EditorFrame';
import PageDetails from './PageDetails';
import useOutletPage from './useOutletPage';
import type { EditorConfig } from './types';

export type EditorState = { busy: boolean; disabled: boolean };
type ReviewedEntity = { kind: string; name: string; key?: number | string };

export default function OutletEditor( {
	config,
	workspace,
	settingsPanels,
}: {
	config: EditorConfig;
	workspace?: ( state: EditorState ) => ReactNode;
	settingsPanels?: ( state: EditorState ) => ReactNode;
} ) {
	const details = useOutletPage( config.pageId );
	const { editEntityRecord } = useDispatch( coreStore );
	const registry = useRegistry();
	const { isDirty } = useEntitiesSavedStatesIsDirty();
	const busy = useSelect(
		( select ) => {
			const core = select( coreStore );
			return (
				core.isSavingEntityRecord( 'root', 'site' ) ||
				core.isSavingEntityRecord( 'postType', 'page', config.pageId )
			);
		},
		[ config.pageId ]
	);
	const [ reviewing, setReviewing ] = useState( false );
	const trigger = useRef< HTMLElement | null >( null );
	const pendingStatus = useRef< { status: unknown; date: unknown } | null >(
		null
	);
	const state = { busy, disabled: busy };
	useEffect( () => {
		if ( reviewing ) {
			return;
		}
		if ( busy ) {
			return;
		}
		if ( trigger.current === null ) {
			return;
		}
		if (
			trigger.current.isConnected &&
			! trigger.current.hasAttribute( 'disabled' )
		) {
			trigger.current.focus();
		} else {
			document
				.getElementById( 'outletpro-editor-settings-toggle' )
				?.focus();
		}
	}, [ reviewing, busy ] );
	function review( nextStatus?: 'publish' | 'draft', button?: HTMLElement ) {
		trigger.current =
			button ??
			document.getElementById( 'outletpro-editor-settings-toggle' );
		if ( nextStatus !== undefined ) {
			if ( ! config.canPublish ) {
				throw new Error( 'Outlet page publishing is not permitted.' );
			}
			if ( details.page === null ) {
				throw new Error( 'Outlet page details are unavailable.' );
			}
			const edits = registry
				.select( coreStore )
				.getEntityRecordEdits( 'postType', 'page', config.pageId );
			pendingStatus.current = {
				status: edits?.status,
				date: edits?.date,
			};
			editEntityRecord( 'postType', 'page', config.pageId, {
				status: nextStatus,
				...( nextStatus === 'publish'
					? { date: new Date().toISOString() }
					: {} ),
			} );
		}
		setReviewing( true );
	}
	function closeReview( savedEntities?: ReviewedEntity[] ) {
		const pageSelected = savedEntities?.some(
			( entity ) =>
				entity.kind === 'postType' &&
				entity.name === 'page' &&
				entity.key === config.pageId
		);
		if ( ! pageSelected ) {
			if ( pendingStatus.current !== null ) {
				editEntityRecord(
					'postType',
					'page',
					config.pageId,
					pendingStatus.current
				);
			}
		}
		pendingStatus.current = null;
		setReviewing( false );
	}
	return (
		<EditorFrame
			title={ details.title || undefined }
			link={ details.page?.link }
			disabled={ busy }
			notices={
				<>
					<InlineNotices />
					<SnackbarNotices />
				</>
			}
			workspace={
				<>
					<UnsavedChangesWarning />
					{ details.error && (
						<Notice status="error" isDismissible={ false }>
							{ details.error }
						</Notice>
					) }
					{ workspace?.( state ) }
				</>
			}
			settings={
				<>
					<PageDetails
						{ ...details }
						disabled={ busy }
						onTitleChange={ details.setTitle }
						onSlugChange={ details.setSlug }
						onUnpublish={
							config.canPublish
								? () => review( 'draft' )
								: undefined
						}
					/>
					{ settingsPanels?.( state ) }
				</>
			}
			actions={
				<>
					<Button
						variant="primary"
						size="compact"
						disabled={ details.page === null || busy || ! isDirty }
						isBusy={ busy }
						onClick={ ( event: MouseEvent< HTMLButtonElement > ) =>
							review( undefined, event.currentTarget )
						}
					>
						{ __( 'Save', 'outletpro' ) }
					</Button>
					{ details.page !== null &&
						details.page.status !== 'publish' &&
						config.canPublish && (
							<Button
								variant="secondary"
								size="compact"
								disabled={ busy }
								onClick={ (
									event: MouseEvent< HTMLButtonElement >
								) => review( 'publish', event.currentTarget ) }
							>
								{ __( 'Publish', 'outletpro' ) }
							</Button>
						) }
				</>
			}
			review={
				reviewing ? (
					<EntitiesSavedStates close={ closeReview } renderDialog />
				) : undefined
			}
			onCloseReview={ closeReview }
		/>
	);
}
