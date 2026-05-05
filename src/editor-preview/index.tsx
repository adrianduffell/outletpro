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
		const handleCanvasReady = ( event: Event ) => {
			const canvasDoc = ( event as CustomEvent< { document: Document } > ).detail.document;
			setTargetDoc( canvasDoc );
		};

		// Watch for custom event signalling when canvas iframe is ready.
		window.addEventListener(
			'wc-clearance-canvas-ready',
			handleCanvasReady,
			true
		);

		// Cleanup listener on unmount.
		return () => {
			window.removeEventListener(
				'wc-clearance-canvas-ready',
				handleCanvasReady,
				true
			);
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

	// Portal needed because the editor canvas is not in the same document as the plugin script.
	return createPortal(
		<style id="wc-clearance-preview-vars">{ styleText }</style>,
		targetDoc.head
	);
};

export default EditorPreview;
