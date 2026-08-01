import { registerPlugin } from '@wordpress/plugins';

// #ifdef LICENSE
import './welcome-page';
// #endif
import { OutletEmptyNotice } from './page-editor-notice';
import {
	SettingsSidebar,
	withSiteRecord,
	SIDEBAR_NAME,
} from './settings-sidebar';
import './outlet-toggle';
import EditorPreview from './editor-preview';
import './blocks/outlet-badge';
import './blocks/outlet-message';

registerPlugin( 'outletpro-page-editor-notice', {
	render: OutletEmptyNotice,
} );

registerPlugin( 'outletpro-editor-preview', {
	render: EditorPreview,
} );

// Only register the sidebar in the Site Editor (site-editor.php).
if ( window.location.pathname.includes( 'site-editor.php' ) ) {
	registerPlugin( SIDEBAR_NAME, {
		render: withSiteRecord( SettingsSidebar ),
	} );
}
