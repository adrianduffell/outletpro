import { registerPlugin } from '@wordpress/plugins';

import { Sample } from './components/sample';
import './page-editor-notice';
import './page-editor-sidebar';
import EditorPreview from './editor-preview';
import './blocks/clearance-badge';
import './blocks/clearance-message';
import './blocks/product-collection';

registerPlugin( 'wc-clearance-editor-preview', {
	render: EditorPreview,
} );

export { Sample };

