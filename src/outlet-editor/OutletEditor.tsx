/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import type { ReactNode } from 'react';
import { Notice } from '@wordpress/components';
import EditorFrame from './EditorFrame';
import PageDetails from './PageDetails';
import useOutletPage from './useOutletPage';
import type { EditorConfig } from './types';

export default function OutletEditor( {
	config,
	workspace,
	settingsPanels,
}: {
	config: EditorConfig;
	workspace?: ReactNode;
	settingsPanels?: ReactNode;
} ) {
	const details = useOutletPage( config.pageId );
	return (
		<EditorFrame
			title={ details.page?.title.raw }
			link={ details.page?.link }
			workspace={
				<>
					{ details.error && (
						<Notice status="error" isDismissible={ false }>
							{ details.error }
						</Notice>
					) }
					{ workspace }
				</>
			}
			settings={
				<>
					<PageDetails { ...details } />
					{ settingsPanels }
				</>
			}
		/>
	);
}
