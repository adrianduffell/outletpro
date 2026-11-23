/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import type { ReactNode, Ref } from 'react';
import {
	Button,
	Toolbar,
	ToolbarGroup,
	ToolbarButton,
} from '@wordpress/components';
// eslint-disable-next-line import/no-extraneous-dependencies -- WordPress supplies this external; it is already resolved in the lockfile.
import { store as commandsStore } from '@wordpress/commands';
import { useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import {
	Icon,
	chevronLeft,
	drawerRight,
	external,
	layout,
} from '@wordpress/icons';
import './navigation.css';

export default function EditorNavigation( {
	title,
	link,
	settingsOpen,
	onToggleSettings,
	settingsButton,
	disabled,
	actions,
}: {
	title: string;
	link?: string;
	settingsOpen: boolean;
	onToggleSettings: () => void;
	settingsButton: Ref< HTMLButtonElement >;
	disabled: boolean;
	actions?: ReactNode;
} ) {
	const { open } = useDispatch( commandsStore );
	return (
		<header className="outletpro-editor-header">
			<Toolbar
				label={ __( 'Outlet navigation', 'outletpro' ) }
				className="outletpro-editor-navigation"
			>
				<Button
					href={
						new URL(
							'edit.php?post_type=page',
							window.location.href
						).href
					}
					icon={ chevronLeft }
					label={ __( 'Back to pages', 'outletpro' ) }
					size="compact"
				/>
			</Toolbar>
			<Button
				className="outletpro-editor-document"
				onClick={ open }
				label={ __( 'Search commands and settings', 'outletpro' ) }
				aria-haspopup="dialog"
				size="compact"
			>
				<Icon
					className="outletpro-editor-document-icon"
					icon={ layout }
					size={ 24 }
				/>
				<span className="outletpro-editor-document-title">
					{ title }
					<span className="outletpro-editor-document-type">
						{ __( '· Page', 'outletpro' ) }
					</span>
				</span>
			</Button>
			<Toolbar
				label={ __( 'Page actions', 'outletpro' ) }
				className="outletpro-editor-header-actions"
			>
				<ToolbarGroup>
					{ link && (
						<Button
							href={ link }
							target="_blank"
							rel="noopener noreferrer"
							icon={ external }
							label={ __( 'View outlet', 'outletpro' ) }
							size="compact"
						/>
					) }
					<ToolbarButton
						ref={ settingsButton }
						icon={ drawerRight }
						label={ __( 'Page settings', 'outletpro' ) }
						isPressed={ settingsOpen }
						aria-expanded={ settingsOpen }
						aria-controls="outletpro-editor-sidebar"
						onClick={ onToggleSettings }
						disabled={ disabled }
						size="compact"
					/>
					{ actions }
				</ToolbarGroup>
			</Toolbar>
		</header>
	);
}
