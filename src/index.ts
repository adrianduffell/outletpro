import { registerPlugin } from '@wordpress/plugins';

import { Sample } from './components/sample';
import './page-editor-notice';
import './page-editor-sidebar';
import EditorPreview from './editor-preview';
import './blocks/outlet-badge';
import './blocks/outlet-message';
import './blocks/outlet-filter-sort';
import './blocks/product-collection';

registerPlugin( 'wc-outlet-editor-preview', {
	render: EditorPreview,
} );

export { Sample };
