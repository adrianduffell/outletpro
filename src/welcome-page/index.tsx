/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { createRoot } from '@wordpress/element';
import { WelcomePage } from './WelcomePage';
import './style.css';

const container = document.getElementById( 'outletpro-welcome-page-root' );

if ( container ) {
	const root = createRoot( container );
	root.render( <WelcomePage /> );
}
