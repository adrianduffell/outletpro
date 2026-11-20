/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { createRoot } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import Manager from './outlet-manager/Manager';
import type { ManagerConfig } from './outlet-manager/types';
import './outlet-manager/style.css';

const container = document.getElementById( 'outletpro-outlet-manager' );
if ( container !== null ) {
	try {
		const config: ManagerConfig = JSON.parse(
			container.dataset.config ?? ''
		);
		createRoot( container ).render( <Manager config={ config } /> );
	} catch ( error ) {
		// eslint-disable-next-line no-console -- Log system failures at the API boundary.
		console.error( 'Outlet manager could not be initialized.', error );
		container.textContent = __(
			'The outlet manager could not be loaded. Please refresh and try again.',
			'outletpro'
		);
	}
}
