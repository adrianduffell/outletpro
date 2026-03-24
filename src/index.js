/**
 * Clearance page guide for the block editor.
 *
 * Displays a guide when the user first edits the clearance section page.
 */

import { registerPlugin } from '@wordpress/plugins';
import { ClearanceGuide } from './components/page-guide';

registerPlugin( 'wc-clearance-guide', { render: ClearanceGuide } );
