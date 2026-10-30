/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import type { ReactNode } from 'react';
import { createPortal, useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import './style.css';

export function CatalogPageDescription( {
	children,
}: {
	children?: ReactNode;
} ): JSX.Element | null {
	const [ mountNode, setMountNode ] = useState< HTMLElement | null >( null );

	useEffect( () => {
		const target = document.createElement( 'div' );
		target.className = 'outletpro-page-description';

		function attach() {
			if ( target.isConnected ) {
				return;
			}
			// WordPress has no public insertion point beneath the sidebar title.
			const header = document.querySelector(
				'.editor-post-summary .editor-post-card-panel__header'
			);
			if ( header === null ) {
				return;
			}
			header.after( target );
			setMountNode( target );
		}

		// The sidebar is mounted again when its visibility or selected tab changes.
		const observer = new MutationObserver( attach );
		observer.observe( document.body, { childList: true, subtree: true } );
		attach();

		return () => {
			observer.disconnect();
			target.remove();
		};
	}, [] );

	if ( mountNode === null ) {
		return null;
	}
	return createPortal(
		<>
			<p>{ __( 'Displays products in the outlet.', 'outletpro' ) }</p>
			{ children }
		</>,
		mountNode
	);
}
