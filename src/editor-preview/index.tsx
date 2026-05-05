import { createPortal, useEffect, useState } from '@wordpress/element';
import useSettings from '../use-settings';

const EditorPreview = () => {
	const {
		label,
		textColor,
		bgColor,
		fontSize,
		fontWeight,
		borderColor,
		borderStyle,
		borderWidth,
		borderRadius,
		paddingTop,
		paddingRight,
		paddingBottom,
		paddingLeft,
	} = useSettings();

	const [ targetDoc, setTargetDoc ] = useState< Document >( document );

	useEffect( () => {
		// Keep references to the current iframe and document so we can:
		// - Avoid re-binding listeners unnecessarily
		// - Skip state updates when the document hasn't changed
		// - Clean up correctly when they change
		let iframe: HTMLIFrameElement | null = null;
		let currentDoc: Document = document;

		const update = () => {
			// The Site Editor dynamically mounts/replaces the canvas iframe.
			// We must re-query each time because the DOM node itself can change.
			const nextIframe = document.querySelector(
				'iframe[name="editor-canvas"]'
			) as HTMLIFrameElement | null;

			// If the iframe instance has changed:
			// - Remove old listener
			// - Attach to the new one
			if ( nextIframe !== iframe ) {
				iframe?.removeEventListener( 'load', update );
				iframe = nextIframe;
				iframe?.addEventListener( 'load', update );
			}

			// Only update state when the document actually changes to avoid
			// unnecessary re-renders on unrelated DOM mutations.
			const nextDoc = iframe?.contentDocument ?? document;
			if ( nextDoc !== currentDoc ) {
				currentDoc = nextDoc;
				setTargetDoc( nextDoc );
			}
		};

		// Initial run (covers first render)
		update();

		// MutationObserver is used ONLY to detect when the editor swaps the iframe.
		// This happens on:
		// - Template changes
		// - Navigation within the Site Editor
		// - Some block editor re-renders
		//
		// There is no official WP hook/event for this, so we watch the DOM.
		//
		// Important:
		// - We do NOT mutate the DOM here
		// - We only detect changes and let React handle rendering via portal
		const observer = new MutationObserver( () => {
			update();
		} );

		// Observe the entire body because the iframe can be replaced anywhere
		observer.observe( document.body, {
			childList: true,
			subtree: true,
		} );

		return () => {
			// Clean up:
			// - Remove load listener from last iframe
			// - Disconnect observer to avoid memory leaks
			iframe?.removeEventListener( 'load', update );
			observer.disconnect();
		};
	}, [] );

	const entries = {
		'--wc-clearance-badge-bg-color': bgColor,
		'--wc-clearance-badge-text-color': textColor,
		'--wc-clearance-badge-font-size': fontSize,
		'--wc-clearance-badge-font-weight': fontWeight,
		'--wc-clearance-badge-border-color': borderColor,
		'--wc-clearance-badge-border-style': borderStyle,
		'--wc-clearance-badge-border-width': borderWidth,
		'--wc-clearance-badge-border-radius': borderRadius,
		'--wc-clearance-badge-padding-top': paddingTop,
		'--wc-clearance-badge-padding-right': paddingRight,
		'--wc-clearance-badge-padding-bottom': paddingBottom,
		'--wc-clearance-badge-padding-left': paddingLeft,
	};

	const styleText = `:root { ${ [
		`--wc-clearance-badge-label: ${ JSON.stringify( label ?? '' ) }`,
		...Object.entries( entries ).map(
			( [ key, value ] ) => `${ key }: ${ value ?? 'unset' }`
		),
	].join( '; ' ) } }`;

	return createPortal(
		<style id="wc-clearance-preview-vars">{ styleText }</style>,
		targetDoc.head
	);
};

export default EditorPreview;
