/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { registerPlugin } from '@wordpress/plugins';

// #ifdef LICENSE
import './welcome-page';
// #endif
import './page-editor-notice';
import OutletPageEditorCallout from './page-editor-callout';
import './settings-sidebar';
import './outlet-toggle';
import EditorPreview from './editor-preview';
import './blocks/outlet-badge';
import './blocks/outlet-message';

registerPlugin( 'outletpro-editor-preview', {
	render: EditorPreview,
} );

if ( window.location.pathname.endsWith( '/post.php' ) ) {
	registerPlugin( 'outletpro-page-editor-callout', {
		render: OutletPageEditorCallout,
	} );
}
