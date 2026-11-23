/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import {
	Button,
	Modal,
	Notice,
	Spinner,
	TextControl,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import { Icon, page as pageIcon, pencil } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';
import type { PageRecord } from './types';
import './page-details.css';

const statuses: Record< string, string > = {
	publish: __( 'Published', 'outletpro' ),
	draft: __( 'Draft', 'outletpro' ),
	future: __( 'Scheduled', 'outletpro' ),
	private: __( 'Private', 'outletpro' ),
	pending: __( 'Pending review', 'outletpro' ),
};

export default function PageDetails( {
	page,
	error,
	title = page?.title.raw ?? '',
	slug = page?.slug ?? '',
	onTitleChange,
	onSlugChange,
	onUnpublish,
	disabled = false,
}: {
	page: PageRecord | null;
	error: string;
	title?: string;
	slug?: string;
	onTitleChange?: ( value: string ) => void;
	onSlugChange?: ( value: string ) => void;
	onUnpublish?: () => void;
	disabled?: boolean;
} ) {
	const [ renaming, setRenaming ] = useState( false );
	if ( error ) {
		return (
			<Notice status="error" isDismissible={ false }>
				{ error }
			</Notice>
		);
	}
	if ( page === null ) {
		return <Spinner />;
	}
	return (
		<div className="outletpro-editor-page-details">
			<div className="outletpro-editor-page-card">
				<Icon icon={ pageIcon } size={ 24 } />
				<h2>{ title || __( 'Outlet', 'outletpro' ) }</h2>
				{ onTitleChange && (
					<Button
						icon={ pencil }
						label={ __( 'Rename page', 'outletpro' ) }
						disabled={ disabled }
						onClick={ () => setRenaming( true ) }
						size="compact"
					/>
				) }
			</div>
			<p>{ __( 'You are customizing the outlet page.', 'outletpro' ) }</p>
			<dl>
				<dt>{ __( 'Status', 'outletpro' ) }</dt>
				<dd>{ statuses[ page.status ] ?? page.status }</dd>
				{ ! onSlugChange && (
					<>
						<dt>{ __( 'Slug', 'outletpro' ) }</dt>
						<dd>{ slug }</dd>
					</>
				) }
			</dl>
			{ onSlugChange && (
				<TextControl
					label={ __( 'Slug', 'outletpro' ) }
					value={ slug }
					onChange={ onSlugChange }
					disabled={ disabled }
					__next40pxDefaultSize
					__nextHasNoMarginBottom
				/>
			) }
			{ page.status === 'publish' && onUnpublish && (
				<Button
					variant="secondary"
					disabled={ disabled }
					onClick={ onUnpublish }
				>
					{ __( 'Unpublish', 'outletpro' ) }
				</Button>
			) }
			{ renaming && onTitleChange && (
				<Modal
					title={ __( 'Rename page', 'outletpro' ) }
					onRequestClose={ () => setRenaming( false ) }
					focusOnMount="firstContentElement"
				>
					<TextControl
						label={ __( 'Name', 'outletpro' ) }
						value={ title }
						onChange={ onTitleChange }
						disabled={ disabled }
						__next40pxDefaultSize
						__nextHasNoMarginBottom
					/>
					<Button
						variant="primary"
						onClick={ () => setRenaming( false ) }
					>
						{ __( 'Done', 'outletpro' ) }
					</Button>
				</Modal>
			) }
		</div>
	);
}
