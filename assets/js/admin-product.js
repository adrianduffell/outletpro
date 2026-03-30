/**
 * Admin product editor scripts.
 *
 * @package WC_Clearance
 * @since 1.0.0
 */

/**
 * Replace the description span next to the clearance checkbox with a label
 * element so that clicking the text toggles the checkbox.
 *
 * WooCommerce's woocommerce_wp_checkbox() API renders the description as a
 * <span> rather than a <label> — a limitation of the WooCommerce field
 * rendering API that does not expose a way to change the wrapping element.
 * This script works around it by swapping the element type after DOM load.
 */
( function () {
	document.addEventListener( 'DOMContentLoaded', function () {
		var span = document.querySelector(
			'#wc-clearance-status_field span.description'
		);

		if ( ! span ) {
			return;
		}

		var label = document.createElement( 'label' );
		label.setAttribute( 'for', 'wc-clearance-status' );
		label.textContent = span.textContent;
		label.className = span.className;
		span.parentNode.replaceChild( label, span );
	} );
} )();
