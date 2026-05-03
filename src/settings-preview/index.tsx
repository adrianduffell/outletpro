import { useEffect } from '@wordpress/element';
import useSettings from '../settings/use-settings';

const ClearancePreviewStyles = () => {
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

	useEffect( () => {
		const applyPreviewVars = () => {
			const iframe = document.querySelector(
				'iframe[name="editor-canvas"]'
			) as HTMLIFrameElement | null;
			const targetDoc = iframe?.contentDocument ?? document;

			if ( ! targetDoc.head ) {
				return;
			}

			let styleEl = targetDoc.getElementById(
				'wc-clearance-preview-vars'
			) as HTMLStyleElement | null;

			if ( ! styleEl ) {
				styleEl = targetDoc.createElement( 'style' );
				styleEl.id = 'wc-clearance-preview-vars';
				targetDoc.head.appendChild( styleEl );
			}

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
				`--wc-clearance-badge-label: ${ JSON.stringify(
					label ?? ''
				) } !important`,
				...Object.entries( entries ).map(
					( [ key, value ] ) =>
						`${ key }: ${ value ?? 'unset' } !important`
				),
			].join( '; ' ) } }`;

			styleEl.textContent = styleText;
		};

		applyPreviewVars();

		let iframe = document.querySelector(
			'iframe[name="editor-canvas"]'
		) as HTMLIFrameElement | null;

		iframe?.addEventListener( 'load', applyPreviewVars );

		const observer = new MutationObserver( () => {
			const nextIframe = document.querySelector(
				'iframe[name="editor-canvas"]'
			) as HTMLIFrameElement | null;

			if ( nextIframe !== iframe ) {
				iframe?.removeEventListener( 'load', applyPreviewVars );
				iframe = nextIframe;
				iframe?.addEventListener( 'load', applyPreviewVars );
			}

			applyPreviewVars();
		} );

		observer.observe( document.body, {
			childList: true,
			subtree: true,
		} );

		return () => {
			iframe?.removeEventListener( 'load', applyPreviewVars );
			observer.disconnect();
		};
	}, [
		label,
		bgColor,
		textColor,
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
	] );

	return null;
};

export default ClearancePreviewStyles;
