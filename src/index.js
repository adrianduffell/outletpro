/**
 * Clearance page guide for the block editor.
 *
 * Displays a guide when the user first edits the clearance section page.
 */

import { Guide } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as preferencesStore } from '@wordpress/preferences';
import { registerPlugin } from '@wordpress/plugins';

const PREFERENCE_SCOPE = 'wc-clearance';
const PREFERENCE_KEY = 'hasSeenClearanceGuide';

function ClearanceGuide() {
	const isWelcomeGuideActive = useSelect( ( select ) => {
		return (
			select( preferencesStore ).get(
				'core/edit-post',
				'welcomeGuide'
			) !== false
		);
	} );

	const hasSeenGuide = useSelect( ( select ) => {
		return !! select( preferencesStore ).get(
			PREFERENCE_SCOPE,
			PREFERENCE_KEY
		);
	} );

	const { set } = useDispatch( preferencesStore );

	if ( hasSeenGuide || isWelcomeGuideActive ) {
		return null;
	}

	return (
		<Guide
			onFinish={ () => set( PREFERENCE_SCOPE, PREFERENCE_KEY, true ) }
			pages={ [
				{
					content: (
						<p>
							This page shows products in your clearance section.
							Customize it to suit your store.
						</p>
					),
				},
				{
					content: (
						<p>
							Publish the page to make it visible in your store.
						</p>
					),
				},
			] }
		/>
	);
}

registerPlugin( 'wc-clearance-guide', { render: ClearanceGuide } );
