/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { createRoot } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import OutletEditorScreen from './outlet-editor/OutletEditorScreen';
import { registerProductEntity } from './data/products';
import type { EditorConfig } from './outlet-editor/types';
import './outlet-editor/style.css';

const container = document.getElementById( 'outletpro-outlet-editor' );
if ( container !== null ) {
	try {
		const config: EditorConfig = JSON.parse(
			container.dataset.config ?? ''
		);
		if ( ! Number.isInteger( config.pageId ) ) {
			throw new Error( 'Outlet page ID is invalid.' );
		}
		if ( config.pageId < 1 ) {
			throw new Error( 'Outlet page ID is invalid.' );
		}
		if ( typeof config.canPublish !== 'boolean' ) {
			throw new Error( 'Outlet publishing permission is invalid.' );
		}
		registerProductEntity();
		createRoot( container ).render(
			<OutletEditorScreen config={ config } />
		);
	} catch {
		container.textContent = __(
			'The outlet editor could not be loaded. Reload the page before continuing.',
			'outletpro'
		);
	}
}
