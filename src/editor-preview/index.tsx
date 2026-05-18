import { createPortal, useEffect, useState } from '@wordpress/element';
import { buildPreviewStyles } from '../utils/build-preview-styles';
import useSettings from '../use-settings';

const EditorPreview = () => {
	const settings = useSettings();

	const [ targetDoc, setTargetDoc ] = useState< Document >( document );

	useEffect( () => {
		const handleCanvasReady = ( event: Event ) => {
			const canvasDoc = ( event as CustomEvent< { document: Document } > )
				.detail.document;
			setTargetDoc( canvasDoc );
		};

		// Watch for custom event signalling when canvas iframe is ready.
		window.addEventListener(
			'wc-outlet-canvas-ready',
			handleCanvasReady,
			true
		);

		// Cleanup listener on unmount.
		return () => {
			window.removeEventListener(
				'wc-outlet-canvas-ready',
				handleCanvasReady,
				true
			);
		};
	}, [] );

	const styleText = buildPreviewStyles( settings );

	// Portal needed because the editor canvas is not in the same document as the plugin script.
	return createPortal(
		<style id="wc-outlet-preview-vars">{ styleText }</style>,
		targetDoc.head
	);
};

export default EditorPreview;
