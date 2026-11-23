/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { createRoot } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import EditorFrame from './outlet-editor/EditorFrame';
import './outlet-editor/style.css';

const container = document.getElementById( 'outletpro-outlet-editor' );
if ( container !== null ) {
	try {
		createRoot( container ).render( <EditorFrame /> );
	} catch ( error ) {
		// eslint-disable-next-line no-console -- Log system failures at the screen boundary.
		console.error( 'Outlet editor could not be initialized.', error );
		container.textContent = __(
			'The outlet editor could not be loaded. Reload the page before continuing.',
			'outletpro'
		);
	}
}
