/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import type { ReactNode } from 'react';
import { Button } from '@wordpress/components';
import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { close } from '@wordpress/icons';
import EditorShell from './EditorShell';
import EditorNavigation from './EditorNavigation';

export default function EditorFrame( {
	title = __( 'Outlet', 'outletpro' ),
	link,
	workspace,
	settings,
	actions,
	notices,
	disabled = false,
	review,
	onCloseReview,
}: {
	title?: string;
	link?: string;
	workspace?: ReactNode;
	settings?: ReactNode;
	actions?: ReactNode;
	notices?: ReactNode;
	disabled?: boolean;
	review?: ReactNode;
	onCloseReview?: () => void;
} ) {
	const [ settingsOpen, setSettingsOpen ] = useState(
		() => window.matchMedia( '(min-width: 960px)' ).matches
	);
	const settingsButton = useRef< HTMLButtonElement >( null );
	const settingsPanel = useRef< HTMLDivElement >( null );
	function closeSettings() {
		setSettingsOpen( false );
		settingsButton.current?.focus();
	}
	useEffect( () => {
		if ( ! settingsOpen ) {
			return;
		}
		function onKeyDown( event: KeyboardEvent ) {
			if ( event.key !== 'Escape' ) {
				return;
			}
			if ( ! ( event.target instanceof Node ) ) {
				return;
			}
			if ( ! settingsPanel.current?.contains( event.target ) ) {
				return;
			}
			closeSettings();
		}
		window.addEventListener( 'keydown', onKeyDown );
		return () => window.removeEventListener( 'keydown', onKeyDown );
	}, [ settingsOpen ] );
	return (
		<EditorShell
			header={
				<EditorNavigation
					title={ title }
					link={ link }
					settingsOpen={ settingsOpen || Boolean( review ) }
					onToggleSettings={ () => {
						if ( review ) {
							onCloseReview?.();
							setSettingsOpen( true );
							return;
						}
						setSettingsOpen( ! settingsOpen );
					} }
					settingsButton={ settingsButton }
					disabled={ disabled }
					actions={ actions }
				/>
			}
			workspace={ workspace }
			notices={ notices }
			sidebar={
				review ??
				( settingsOpen && (
					<div ref={ settingsPanel }>
						<div className="outletpro-editor-sidebar-heading">
							<h2>{ __( 'Page', 'outletpro' ) }</h2>
							<Button
								icon={ close }
								label={ __( 'Close settings', 'outletpro' ) }
								onClick={ closeSettings }
								size="compact"
							/>
						</div>
						{ settings }
					</div>
				) )
			}
		/>
	);
}
