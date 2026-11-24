/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import type { ReactNode } from 'react';
import { __ } from '@wordpress/i18n';

export default function EditorShell( {
	header,
	workspace,
	sidebar,
	review,
	notices,
}: {
	header?: ReactNode;
	workspace?: ReactNode;
	sidebar?: ReactNode;
	review?: ReactNode;
	notices?: ReactNode;
} ) {
	return (
		<div className="outletpro-editor">
			{ header }
			<div
				className={ `outletpro-editor-layout${ sidebar || review ? ' is-sidebar-open' : '' }` }
			>
				<main
					className="outletpro-editor-workspace"
					aria-label={ __( 'Outlet workspace', 'outletpro' ) }
				>
					{ workspace }
				</main>
				{ ! review && sidebar && (
					<aside
						id="outletpro-editor-sidebar"
						className="outletpro-editor-details"
						aria-label={ __( 'Page settings', 'outletpro' ) }
					>
						{ sidebar }
					</aside>
				) }
			</div>
			{ review && (
				<aside
					id="outletpro-editor-sidebar"
					className="outletpro-editor-details outletpro-editor-review"
					aria-label={ __( 'Review changes', 'outletpro' ) }
				>
					{ review }
				</aside>
			) }
			{ notices }
		</div>
	);
}
