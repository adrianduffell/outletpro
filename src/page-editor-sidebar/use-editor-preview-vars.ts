import { useEffect, useRef } from '@wordpress/element';

type PreviewVars = {
	label?: string;
	textColor?: string;
	bgColor?: string;
	fontSize?: string;
	fontWeight?: string;
	borderColor?: string;
	borderStyle?: string;
	borderWidth?: string;
	borderRadius?: string;
	paddingTop?: string;
	paddingRight?: string;
	paddingBottom?: string;
	paddingLeft?: string;
};

const STYLE_ID = 'wc-clearance-preview-vars';

export function buildCss( vars: PreviewVars ): string {
	const entries: string[] = [];

	if ( typeof vars.label === 'string' && vars.label !== '' ) {
		entries.push(
			`--wc-clearance-badge-label: ${ JSON.stringify( vars.label ) };`
		);
	}

	if ( typeof vars.textColor === 'string' && vars.textColor !== '' ) {
		entries.push( `--wc-clearance-badge-text-color: ${ vars.textColor };` );
	}

	if ( typeof vars.bgColor === 'string' && vars.bgColor !== '' ) {
		entries.push( `--wc-clearance-badge-bg-color: ${ vars.bgColor };` );
	}

	if ( typeof vars.fontSize === 'string' && vars.fontSize !== '' ) {
		entries.push( `--wc-clearance-badge-font-size: ${ vars.fontSize };` );
	}

	if ( typeof vars.fontWeight === 'string' && vars.fontWeight !== '' ) {
		entries.push(
			`--wc-clearance-badge-font-weight: ${ vars.fontWeight };`
		);
	}

	if ( typeof vars.borderColor === 'string' && vars.borderColor !== '' ) {
		entries.push(
			`--wc-clearance-badge-border-color: ${ vars.borderColor };`
		);
	}

	if ( typeof vars.borderStyle === 'string' && vars.borderStyle !== '' ) {
		entries.push(
			`--wc-clearance-badge-border-style: ${ vars.borderStyle };`
		);
	}

	if ( typeof vars.borderWidth === 'string' && vars.borderWidth !== '' ) {
		entries.push(
			`--wc-clearance-badge-border-width: ${ vars.borderWidth };`
		);
	}

	if ( typeof vars.borderRadius === 'string' && vars.borderRadius !== '' ) {
		entries.push(
			`--wc-clearance-badge-border-radius: ${ vars.borderRadius };`
		);
	}

	if ( typeof vars.paddingTop === 'string' && vars.paddingTop !== '' ) {
		entries.push(
			`--wc-clearance-badge-padding-top: ${ vars.paddingTop };`
		);
	}

	if ( typeof vars.paddingRight === 'string' && vars.paddingRight !== '' ) {
		entries.push(
			`--wc-clearance-badge-padding-right: ${ vars.paddingRight };`
		);
	}

	if ( typeof vars.paddingBottom === 'string' && vars.paddingBottom !== '' ) {
		entries.push(
			`--wc-clearance-badge-padding-bottom: ${ vars.paddingBottom };`
		);
	}

	if ( typeof vars.paddingLeft === 'string' && vars.paddingLeft !== '' ) {
		entries.push(
			`--wc-clearance-badge-padding-left: ${ vars.paddingLeft };`
		);
	}

	if ( entries.length === 0 ) {
		return '';
	}

	return `:root {\n${ entries.map( ( e ) => `\t${ e }` ).join( '\n' ) }\n}`;
}

function applyToDocument( doc: Document, css: string ): void {
	let el = doc.getElementById( STYLE_ID ) as HTMLStyleElement | null;

	if ( css === '' ) {
		el?.remove();
		return;
	}

	if ( ! el ) {
		el = doc.createElement( 'style' );
		el.id = STYLE_ID;
		doc.head.appendChild( el );
	}

	if ( el.textContent !== css ) {
		el.textContent = css;
	}
}

export function useEditorPreviewVars( vars: PreviewVars ): void {
	const css = buildCss( vars );

	const iframeRef = useRef< HTMLIFrameElement | null >( null );
	const cssRef = useRef( css );
	cssRef.current = css;

	// Effect 1: wire up the MutationObserver and iframe load listener once.
	// Uses cssRef so callbacks always apply the latest CSS without re-wiring.
	useEffect( () => {
		const onLoad = () => {
			if ( iframeRef.current?.contentDocument ) {
				applyToDocument(
					iframeRef.current.contentDocument,
					cssRef.current
				);
			}
		};

		function attachToIframe( iframeEl: HTMLIFrameElement ): void {
			if ( iframeRef.current === iframeEl ) {
				return;
			}
			if ( iframeRef.current ) {
				iframeRef.current.removeEventListener( 'load', onLoad );
			}
			iframeRef.current = iframeEl;
			if ( iframeEl.contentDocument ) {
				applyToDocument( iframeEl.contentDocument, cssRef.current );
			}
			iframeEl.addEventListener( 'load', onLoad );
		}

		const existingIframe = document.querySelector< HTMLIFrameElement >(
			'iframe[name="editor-canvas"]'
		);
		if ( existingIframe ) {
			attachToIframe( existingIframe );
		}

		const observer = new MutationObserver( ( mutations ) => {
			for ( const mutation of mutations ) {
				for ( const node of mutation.addedNodes ) {
					if (
						node instanceof HTMLIFrameElement &&
						node.getAttribute( 'name' ) === 'editor-canvas'
					) {
						attachToIframe( node );
					}
				}
			}
		} );

		observer.observe( document.body, {
			childList: true,
			subtree: true,
		} );

		return () => {
			observer.disconnect();
			iframeRef.current?.removeEventListener( 'load', onLoad );
			applyToDocument( document, '' );
			if ( iframeRef.current?.contentDocument ) {
				applyToDocument( iframeRef.current.contentDocument, '' );
			}
			iframeRef.current = null;
		};
	}, [] );

	// Effect 2: apply CSS to the main document and iframe whenever it changes.
	// No cleanup here — applyToDocument handles removal when css is ''.
	useEffect( () => {
		applyToDocument( document, css );
		if ( iframeRef.current?.contentDocument ) {
			applyToDocument( iframeRef.current.contentDocument, css );
		}
	}, [ css ] );
}
