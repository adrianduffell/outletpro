/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { registerPlugin } from '@wordpress/plugins';
import { registerProductEntity } from './data/products';

// #ifdef LICENSE
import './welcome-page';
// #endif
import './page-editor-notice';
import OutletPageEditorCallout from './page-editor-callout';
import OutletPageEditorDescription from './page-editor-description';
import './settings-sidebar';
import './outlet-toggle';
import EditorPreview from './editor-preview';
import BadgeColorPalette from './badge-color-palette';
import './blocks/outlet-conditional';
import './blocks/outlet-badge';
import './blocks/outlet-message';
import './blocks/outlet-link';
import './blocks/outlet-tile';
import './blocks/outlet-tiles';
import './blocks/outlet-menu';

registerProductEntity();

registerPlugin( 'outletpro-editor-preview', {
	render: EditorPreview,
} );

registerPlugin( 'outletpro-badge-color-palette', {
	render: BadgeColorPalette,
} );

if ( window.location.pathname.endsWith( '/post.php' ) ) {
	registerPlugin( 'outletpro-page-editor-description', {
		render: OutletPageEditorDescription,
	} );
	registerPlugin( 'outletpro-page-editor-callout', {
		render: OutletPageEditorCallout,
	} );
}
