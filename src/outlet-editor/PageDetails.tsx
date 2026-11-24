/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import {
	Notice,
	RadioControl,
	Spinner,
	TextControl,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import {
	Icon,
	drafts,
	notAllowed,
	page as pageIcon,
	pencil,
	pending,
	published,
	scheduled,
} from '@wordpress/icons';
import { __ } from '@wordpress/i18n';
import PageDetailsPopover from './PageDetailsPopover';
import type { EditablePageStatus, PageRecord } from './types';
import './page-details.css';

const statuses: Record< string, string > = {
	publish: __( 'Published', 'outletpro' ),
	draft: __( 'Draft', 'outletpro' ),
	future: __( 'Scheduled', 'outletpro' ),
	private: __( 'Private', 'outletpro' ),
	pending: __( 'Pending review', 'outletpro' ),
};

const statusIcons: Record< string, typeof published > = {
	draft: drafts,
	publish: published,
	future: scheduled,
	private: notAllowed,
	pending,
};

const statusOptions = [
	{
		label: __( 'Draft', 'outletpro' ),
		value: 'draft',
		description: __( 'Not ready to publish.', 'outletpro' ),
	},
	{
		label: __( 'Published', 'outletpro' ),
		value: 'publish',
		description: __( 'Visible to everyone.', 'outletpro' ),
	},
];

export default function PageDetails( {
	page,
	error,
	title = page?.title.raw ?? '',
	slug = page?.slug ?? '',
	status = page?.status ?? '',
	onTitleChange,
	onSlugChange,
	onStatusChange,
	disabled = false,
}: {
	page: PageRecord | null;
	error: string;
	title?: string;
	slug?: string;
	status?: string;
	onTitleChange?: ( value: string ) => void;
	onSlugChange?: ( value: string ) => void;
	onStatusChange?: ( value: EditablePageStatus ) => void;
	disabled?: boolean;
} ) {
	const [ statusAnchor, setStatusAnchor ] =
		useState< HTMLDListElement | null >( null );
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
	const statusLabel = statuses[ status ] ?? status;
	const options = statusOptions.some( ( option ) => option.value === status )
		? statusOptions
		: [
				{ label: statusLabel, value: status, disabled: true },
				...statusOptions,
			];
	return (
		<div className="outletpro-editor-page-details">
			<div className="outletpro-editor-page-card">
				<Icon icon={ pageIcon } size={ 24 } />
				<h2>{ title || __( 'Outlet', 'outletpro' ) }</h2>
				{ onTitleChange && (
					<PageDetailsPopover
						title={ __( 'Rename page', 'outletpro' ) }
						icon={ pencil }
						label={ __( 'Rename page', 'outletpro' ) }
						disabled={ disabled }
					>
						{ () => (
							<TextControl
								label={ __( 'Name', 'outletpro' ) }
								value={ title }
								onChange={ onTitleChange }
								disabled={ disabled }
								__next40pxDefaultSize
								__nextHasNoMarginBottom
							/>
						) }
					</PageDetailsPopover>
				) }
			</div>
			<p>{ __( 'You are customizing the outlet page.', 'outletpro' ) }</p>
			<dl
				ref={ setStatusAnchor }
				className="outletpro-editor-page-properties"
			>
				<dt>{ __( 'Status', 'outletpro' ) }</dt>
				<dd className="outletpro-editor-page-status-field">
					{ onStatusChange ? (
						<PageDetailsPopover
							title={ __( 'Status', 'outletpro' ) }
							value={ statusLabel }
							icon={ statusIcons[ status ] }
							anchor={ statusAnchor }
							offset={ 32 }
							contentClassName="outletpro-editor-status-popover"
							disabled={ disabled }
						>
							{ () => (
								<RadioControl
									label={ __( 'Status', 'outletpro' ) }
									hideLabelFromVision
									options={ options }
									selected={ status }
									disabled={ disabled }
									onChange={ ( value ) => {
										if (
											value !== 'draft' &&
											value !== 'publish'
										) {
											throw new Error(
												'Page status is not supported.'
											);
										}
										onStatusChange( value );
									} }
								/>
							) }
						</PageDetailsPopover>
					) : (
						<span className="outletpro-editor-page-status">
							{ statusIcons[ status ] && (
								<Icon icon={ statusIcons[ status ] } />
							) }
							{ statusLabel }
						</span>
					) }
				</dd>
				<dt>{ __( 'Slug', 'outletpro' ) }</dt>
				<dd>
					{ onSlugChange ? (
						<PageDetailsPopover
							title={ __( 'Slug', 'outletpro' ) }
							value={ slug || __( 'Not set', 'outletpro' ) }
							disabled={ disabled }
						>
							{ () => (
								<TextControl
									label={ __( 'Slug', 'outletpro' ) }
									value={ slug }
									onChange={ onSlugChange }
									disabled={ disabled }
									__next40pxDefaultSize
									__nextHasNoMarginBottom
								/>
							) }
						</PageDetailsPopover>
					) : (
						<span>{ slug }</span>
					) }
				</dd>
			</dl>
		</div>
	);
}
