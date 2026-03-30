/**
 * Admin product editor scripts.
 *
 * @package
 * @since 1.0.0
 */

/**
 * Make the description text next to the clearance checkbox clickable.
 *
 * WooCommerce's woocommerce_wp_checkbox() API renders the description as a
 * <span> rather than a <label> — a limitation of the WooCommerce field
 * rendering API that does not expose a way to change the wrapping element.
 * This script works around it by attaching an onclick handler to the span
 * that toggles the checkbox when the text is clicked.
 * Todo: consider contributing a fix upstream.
 */
( function () {
	document.addEventListener( 'DOMContentLoaded', function () {
		const span = document.querySelector(
			'.wc-clearance-status_field span.description'
		);

		if ( ! span ) {
			return;
		}

		const checkbox = document.getElementById( 'wc-clearance-status' );

		if ( ! checkbox ) {
			return;
		}

		span.style.cursor = 'default';
		span.style.userSelect = 'none';
		span.addEventListener( 'click', function () {
			checkbox.checked = ! checkbox.checked;
			checkbox.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		} );
	} );
} )();
