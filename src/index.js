/**
 * Clearance page tour for the block editor, powered by Driver.js.
 *
 * Displays a 2-step tour when the user first edits the clearance section page.
 * Waits for the Gutenberg welcome guide to be dismissed before starting, so
 * the two do not appear simultaneously.
 */

import { driver } from 'driver.js';
import 'driver.js/dist/driver.css';

import { useEffect, useRef } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { store as preferencesStore } from '@wordpress/preferences';
import { registerPlugin } from '@wordpress/plugins';

const PREFERENCE_SCOPE = 'wc-clearance';
const PREFERENCE_KEY = 'hasSeenClearanceTour';

function ClearanceTour() {
	const tourStarted = useRef( false );

	const isOnClearancePage = useSelect( ( select ) => {
		return (
			select( 'core/editor' ).getCurrentPostId() ===
			window.wcClearance?.pageId
		);
	} );

	const hasSeenTour = useSelect( ( select ) => {
		return !! select( preferencesStore ).get(
			PREFERENCE_SCOPE,
			PREFERENCE_KEY
		);
	} );

	// Avoid starting the tour while the Gutenberg welcome guide is visible.
	const isWelcomeGuideVisible = useSelect( ( select ) => {
		return (
			select( 'core/preferences' )?.get(
				'core/edit-post',
				'welcomeGuide'
			) !== false
		);
	} );

	const { set } = useDispatch( preferencesStore );

	useEffect( () => {
		if (
			! isOnClearancePage ||
			hasSeenTour ||
			isWelcomeGuideVisible ||
			tourStarted.current
		) {
			return;
		}

		tourStarted.current = true;

		const tourInstance = driver( {
			popoverClass: 'wc-clearance-tour',
			onDestroyStarted: () => {
				set( PREFERENCE_SCOPE, PREFERENCE_KEY, true );
				tourInstance.destroy();
			},
			steps: [
				{
					popover: {
						title: 'Clearance section',
						description:
							'This page shows all the products in the clearance section.',
					},
				},
				{
					popover: {
						title: 'Edit and publish',
						description:
							'Edit and publish this page to make it visible to customers.',
					},
				},
			],
		} );

		tourInstance.drive();

		return () => {
			tourStarted.current = false;
			if ( tourInstance.isActive() ) {
				tourInstance.destroy();
			}
		};
	}, [ isOnClearancePage, hasSeenTour, isWelcomeGuideVisible, set ] );

	return null;
}

registerPlugin( 'wc-clearance-tour', { render: ClearanceTour } );
