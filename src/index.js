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

	const shouldShowTour =
		new URLSearchParams( window.location.search ).get(
			'wc-clearance-tour'
		) === '1';
	console.log( 'shouldShowTour', shouldShowTour );
	const isOnClearancePage = useSelect( ( select ) => {
		return select( 'core/editor' ).getCurrentPost()?.status === 'draft';
	} );

	const hasSeenTour = useSelect( ( select ) => {
		return !! select( preferencesStore ).get(
			PREFERENCE_SCOPE,
			PREFERENCE_KEY
		);
	} );
	console.log( 'hasSeenClearanceTour', hasSeenTour );

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
			! shouldShowTour ||
			! isOnClearancePage ||
			hasSeenTour ||
			isWelcomeGuideVisible
		) {
			return;
		}

		tourStarted.current = true;

		const tourInstance = driver( {
			stagePadding: 5,
			overlayOpacity: 0.35,
			animate: false,
			allowClose: true,
			popoverClass: 'wc-clearance-tour',
			showButtons: [ '' ],
			/*onDestroyStarted: () => {
				set( PREFERENCE_SCOPE, PREFERENCE_KEY, true );
				tourInstance.destroy();
			},*/

			onHighlighted: ( element ) => {
				const handlePublishClick = () => {
					tourInstance.destroy();
				};

				element?.addEventListener( 'click', handlePublishClick, {
					once: true,
				} );

				removePublishListener = () => {
					element?.removeEventListener( 'click', handlePublishClick );
				};
			},
			doneBtnText: 'Get started',
			steps: [
				{
					element: '.editor-post-publish-button__button',
					popover: {
						//title: 'Clearance section page',
						description:
							'<p><b>Your clearance section page is ready!</b> <p>Make any changes and publish it to make the page visible to customers.',
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
