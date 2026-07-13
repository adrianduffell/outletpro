import { createRoot } from '@wordpress/element';
import { WelcomePage } from './WelcomePage';

const container = document.getElementById( 'outletpro-welcome-page-root' );

if ( container ) {
	const root = createRoot( container );
	root.render( <WelcomePage /> );
}
