/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import type { MouseEvent, ReactNode } from 'react';
import { Button, Notice } from '@wordpress/components';
import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import EditorFrame from './EditorFrame';
import PageDetails from './PageDetails';
import ReviewChanges from './ReviewChanges';
import useOutletPage from './useOutletPage';
import useEditorChanges from './useEditorChanges';
import type { EditorConfig } from './types';
import type { ChangeSelection, ChangeSource } from './changes';

export type EditorState = { busy: boolean; failed: boolean; disabled: boolean };

const statusDescriptions = {
	publish: __( 'Publish page', 'outletpro' ),
	draft: __( 'Unpublish page', 'outletpro' ),
};

export default function OutletEditor( {
	config,
	workspace,
	settingsPanels,
	changeSources = [],
}: {
	config: EditorConfig;
	workspace?: ( state: EditorState ) => ReactNode;
	settingsPanels?: ( state: EditorState ) => ReactNode;
	changeSources?: ChangeSource[];
} ) {
	const details = useOutletPage( config.pageId );
	const [ reviewing, setReviewing ] = useState( false );
	const [ status, setStatus ] = useState< 'publish' | 'draft' | undefined >();
	const trigger = useRef< HTMLElement | null >( null );
	const pageSource: ChangeSource = {
		id: 'page',
		label: __( 'Page details', 'outletpro' ),
		changes:
			details.dirty || status !== undefined
				? [
						{
							id: 'details',
							label: __( 'Page details', 'outletpro' ),
							description: status && statusDescriptions[ status ],
							details: (
								<dl>
									<dt>{ __( 'Name', 'outletpro' ) }</dt>
									<dd>{ details.title }</dd>
									<dt>{ __( 'Slug', 'outletpro' ) }</dt>
									<dd>{ details.slug }</dd>
								</dl>
							),
						},
					]
				: [],
		save: async () => {
			if ( status !== undefined && ! config.canPublish ) {
				throw new Error( 'Outlet page publishing is not permitted.' );
			}
			await details.save( status );
		},
	};
	const sources = [ ...changeSources, pageSource ];
	const editor = useEditorChanges( sources );
	const state = {
		busy: editor.busy,
		failed: editor.failed,
		disabled: editor.busy || editor.failed,
	};
	useEffect( () => {
		if ( reviewing ) {
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
			return;
		}
		document.getElementById( 'outletpro-editor-settings-toggle' )?.focus();
	}, [ reviewing ] );
	function review( nextStatus?: 'publish' | 'draft', button?: HTMLElement ) {
		trigger.current =
			button ??
			document.getElementById( 'outletpro-editor-settings-toggle' );
		setStatus( nextStatus );
		setReviewing( true );
	}
	function closeReview() {
		setReviewing( false );
		setStatus( undefined );
	}
	async function save( selected: ChangeSelection ) {
		if ( await editor.save( selected ) ) {
			closeReview();
		}
	}
	return (
		<EditorFrame
			title={ details.title || undefined }
			link={ details.page?.link }
			disabled={ state.disabled }
			workspace={
				<>
					{ details.error && (
						<Notice status="error" isDismissible={ false }>
							{ details.error }
						</Notice>
					) }
					{ editor.failed && (
						<Notice status="error" isDismissible={ false }>
							<p>
								{ __(
									'Changes could not be saved. Reload the editor before continuing.',
									'outletpro'
								) }
							</p>
							{ editor.error }
						</Notice>
					) }
					{ workspace?.( state ) }
				</>
			}
			settings={
				<>
					<PageDetails
						{ ...details }
						disabled={ state.disabled }
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
						disabled={
							details.page === null ||
							state.disabled ||
							! editor.dirty
						}
						isBusy={ editor.busy }
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
								disabled={ state.disabled }
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
					<ReviewChanges
						sources={ sources }
						busy={ editor.busy }
						failed={ editor.failed }
						publishing={ status === 'publish' }
						onSave={ save }
						onClose={ closeReview }
					/>
				) : undefined
			}
			onCloseReview={ closeReview }
		/>
	);
}
