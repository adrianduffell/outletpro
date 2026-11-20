/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import {
	Button,
	DropdownMenu,
	Modal,
	PanelBody,
	Spinner,
	TextControl,
} from '@wordpress/components';
import { DataForm } from '@wordpress/dataviews/wp';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Icon, close, moreVertical, page, pencil } from '@wordpress/icons';
import { Tabs } from '@wordpress/ui';
import TierList from './TierList';
import { PAGE_SUMMARY_FIELDS, PAGE_SUMMARY_FORM } from './page-summary';
import type { ManagerConfig } from './types';
import type useOutletPage from './useOutletPage';

export default function PageDetails( {
	details,
	config,
	onClose,
	onUnpublish,
}: {
	details: ReturnType< typeof useOutletPage >;
	config: ManagerConfig;
	onClose: () => void;
	onUnpublish: () => void;
} ) {
	const [ renaming, setRenaming ] = useState( false );
	return (
		<aside
			id="outletpro-manager-sidebar"
			aria-label={ __( 'Page details', 'outletpro' ) }
			className="outletpro-manager-details"
		>
			<Tabs.Root
				defaultValue="page"
				className="outletpro-manager-sidebar-tabs"
			>
				<div className="outletpro-manager-sidebar-heading">
					<Tabs.List
						activateOnFocus={ false }
						aria-label={ __( 'Settings', 'outletpro' ) }
					>
						<Tabs.Tab value="page">
							{ __( 'Page', 'outletpro' ) }
						</Tabs.Tab>
					</Tabs.List>
					<Button
						className="outletpro-manager-sidebar-close"
						size="compact"
						icon={ close }
						label={ __( 'Close settings', 'outletpro' ) }
						onClick={ onClose }
					/>
				</div>
				<Tabs.Panel
					value="page"
					tabIndex={ -1 }
					className="outletpro-manager-sidebar-panel"
				>
					<div className="outletpro-manager-sidebar-content">
						{ details.page === null && ! details.error && (
							<Spinner />
						) }
						{ details.page === null && details.error && (
							<Button
								variant="secondary"
								onClick={ details.retry }
							>
								{ __( 'Retry page details', 'outletpro' ) }
							</Button>
						) }
						{ details.page !== null && (
							<>
								<div className="outletpro-manager-page-card">
									<Icon
										className="outletpro-manager-page-icon"
										icon={ page }
										size={ 24 }
									/>
									<h2>
										{ details.title ||
											__( 'Outlet', 'outletpro' ) }
									</h2>
									<DropdownMenu
										className="outletpro-manager-page-actions"
										icon={ moreVertical }
										label={ __(
											'Page actions',
											'outletpro'
										) }
										toggleProps={ {
											size: 'small',
											disabled: details.busy,
										} }
										controls={ [
											{
												title: __(
													'Rename',
													'outletpro'
												),
												icon: pencil,
												onClick: () =>
													setRenaming( true ),
											},
										] }
									/>
								</div>
								<p className="outletpro-manager-page-description">
									{ __(
										'You are customizing the outlet page.',
										'outletpro'
									) }
								</p>
								<DataForm
									data={ {
										status: details.page.status,
										slug: details.slug,
										busy: details.busy,
										unpublish: onUnpublish,
									} }
									fields={ PAGE_SUMMARY_FIELDS }
									form={ PAGE_SUMMARY_FORM }
									onChange={ ( edits ) => {
										if ( typeof edits.slug !== 'string' ) {
											return;
										}
										details.setSlug( edits.slug );
										details.setSaved( false );
									} }
								/>
							</>
						) }
					</div>
					<TierList kind="discount" tiers={ config.discountTiers } />
					<TierList kind="price" tiers={ config.priceBands } />
					{ config.settingsUrl && (
						<PanelBody
							className="outletpro-manager-appearance"
							title={ __( 'Outlet settings', 'outletpro' ) }
							initialOpen={ true }
						>
							<p className="outletpro-manager-help">
								{ __(
									'Customize the outlet badge, message, and appearance.',
									'outletpro'
								) }
							</p>
							<a
								href={ config.settingsUrl }
								target="_blank"
								rel="noopener noreferrer"
							>
								{ __(
									'Outlet appearance settings',
									'outletpro'
								) }
							</a>
						</PanelBody>
					) }
				</Tabs.Panel>
			</Tabs.Root>
			{ renaming && (
				<Modal
					title={ __( 'Rename page', 'outletpro' ) }
					onRequestClose={ () => setRenaming( false ) }
					focusOnMount="firstContentElement"
				>
					<TextControl
						label={ __( 'Name', 'outletpro' ) }
						value={ details.title }
						disabled={ details.busy }
						onChange={ ( value ) => {
							details.setTitle( value );
							details.setSaved( false );
						} }
						__next40pxDefaultSize
						__nextHasNoMarginBottom
					/>
					<div className="outletpro-manager-rename-actions">
						<Button
							variant="primary"
							onClick={ () => setRenaming( false ) }
						>
							{ __( 'Done', 'outletpro' ) }
						</Button>
					</div>
				</Modal>
			) }
		</aside>
	);
}
