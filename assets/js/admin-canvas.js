/**
 * Admin canvas scripts.
 *
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

/**
 * Fire a custom event when the editor canvas iframe is ready.
 *
 * This is used by the editor preview component to bind to the canvas iframe
 * document and inject new styles.
 *
 * Todo: Consider contributing a patch upstream to provide a built-in way for block
 * editor scripts to target the canvas iframe document.
 */
( () => {
	document.addEventListener( 'DOMContentLoaded', () => {
		const isEditorCanvas =
			window.name === 'editor-canvas' ||
			window.frameElement?.name === 'editor-canvas';

		if ( isEditorCanvas && window.parent !== window ) {
			window.parent.dispatchEvent(
				new CustomEvent( 'outletproCanvasReady', {
					detail: { document },
				} )
			);
		}
	} );
} )();
