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
			className="wc-clearance-page-guide"
			contentLabel="Clearance section tour guide"
			onFinish={ () => set( PREFERENCE_SCOPE, PREFERENCE_KEY, true ) }
			pages={ [
				{
					content: (
						<>
							<h1 className="wc-clearance-page-guide__heading">
								Clearance section
							</h1>
							<p className="wc-clearance-page-guide__text">
								This page shows all the products in the
								clearance section.
							</p>
						</>
					),
				},
				{
					content: (
						<>
							<h1 className="wc-clearance-page-guide__heading">
								Edit and publish
							</h1>
							<p className="wc-clearance-page-guide__text">
								Edit and publish this page to make it visible to
								customers.
							</p>
						</>
					),
				},
			] }
		/>
	);
}

registerPlugin( 'wc-clearance-guide', { render: ClearanceGuide } );
