import { registerPlugin } from '@wordpress/plugins';

import { Sample } from './components/sample';
// #ifdef LICENSE
import './welcome-page';
// #endif
import './page-editor-notice';
import './settings-sidebar';
import './outlet-toggle';
import EditorPreview from './editor-preview';
import './blocks/outlet-badge';
import './blocks/outlet-message';

registerPlugin( 'outletpro-editor-preview', {
	render: EditorPreview,
} );

export { Sample };
