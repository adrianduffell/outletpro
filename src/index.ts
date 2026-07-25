import { registerPlugin } from '@wordpress/plugins';

import { Sample } from './components/sample';
import './welcome-page';
import './page-editor-notice';
import './page-editor-sidebar';
import './outlet-toggle';
import EditorPreview from './editor-preview';
import './blocks/outlet-badge';
import './blocks/outlet-message';

registerPlugin( 'outletpro-editor-preview', {
	render: EditorPreview,
} );

export { Sample };
