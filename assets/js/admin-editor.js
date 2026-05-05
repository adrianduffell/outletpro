document.addEventListener( 'DOMContentLoaded', () => {
	window.parent.dispatchEvent(
		new CustomEvent( 'wc-clearance-canvas-ready', {
			detail: document,
		} )
	);
} );
