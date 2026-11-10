/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import type { ReactNode } from 'react';
import { __ } from '@wordpress/i18n';
import { layout } from '@wordpress/icons';
import './style.css';

export function PageEditorCallout( {
	children,
}: {
	children: ReactNode;
} ): JSX.Element {
	return (
		<div className="outletpro-page-editor-callout">
			<span
				className="outletpro-page-editor-callout__icon"
				role="img"
				aria-label={ __( 'Outlet page template', 'outletpro' ) }
			>
				{ layout }
			</span>
			<h3>{ __( 'Outlet page template', 'outletpro' ) }</h3>
			{ children }
		</div>
	);
}
