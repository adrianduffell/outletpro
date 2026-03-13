/**
 * Editor guide for the clearance page.
 *
 * Registers a two-step NUX guide that appears the first time a user
 * opens the clearance page in the block editor.
 */

import { __ } from '@wordpress/i18n';
import { dispatch, select } from '@wordpress/data';
import { DotTip } from '@wordpress/nux';
import { registerPlugin } from '@wordpress/plugins';

/**
 * Renders the two NUX DotTip steps for the clearance page guide.
 */
function ClearanceEditorGuide() {
	return (
		<>
			<DotTip tipId="wc-clearance-guide-intro">
				{ __(
					'This page shows the clearance products.',
					'wc-clearance'
				) }
			</DotTip>
			<DotTip tipId="wc-clearance-guide-publish">
				{ __(
					'Publish the page to make it visible in your store.',
					'wc-clearance'
				) }
			</DotTip>
		</>
	);
}

registerPlugin( 'wc-clearance-editor-guide', {
	render: ClearanceEditorGuide,
} );

// Trigger the guide only if the intro tip has not yet been dismissed.
const nuxSelect = select( 'core/nux' );
if ( nuxSelect && nuxSelect.isTipVisible( 'wc-clearance-guide-intro' ) ) {
	dispatch( 'core/nux' ).triggerGuide( [
		'wc-clearance-guide-intro',
		'wc-clearance-guide-publish',
	] );
}
