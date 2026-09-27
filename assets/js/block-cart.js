/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

( function () {
	'use strict';

	const { registerCheckoutFilters } = window.wc.blocksCheckout;

	// Add .outletpro-cart-item class name to outlet products.
	const modifyCartItemClass = ( defaultValue, extensions ) => {
		if ( extensions?.outletpro?.is_outlet !== true ) {
			return defaultValue;
		}

		if ( '' === defaultValue ) {
			return 'outletpro-cart-item';
		}

		return defaultValue + ' outletpro-cart-item';
	};

	registerCheckoutFilters( 'outletpro', {
		cartItemClass: modifyCartItemClass,
	} );

	const rowSelector = '.outletpro-cart-item';
	const badgeSelector = '.outletpro-badge';
	const label = window.__experimentalOutletProBlockCart.badgeLabel;

	if ( '' === label.trim() ) {
		return;
	}

	// Insert outlet badges where the cart item has .outletpro-cart-item class name.
	const insertBadges = () => {
		for ( const row of document.querySelectorAll( rowSelector ) ) {
			if ( row.querySelector( badgeSelector ) ) {
				continue;
			}

			// Select the anchor point used by block cart item rows.
			const anchor = row.querySelector( '.wc-block-cart-item__prices' );

			if ( ! anchor ) {
				continue;
			}

			const badge = document.createElement( 'div' );
			badge.className = 'outletpro-badge';
			badge.textContent = label;

			const badgeContainer = document.createElement( 'div' );
			badgeContainer.className = 'outletpro-badge-container';
			badgeContainer.append( badge );
			anchor.after( badgeContainer );
		}
	};

	insertBadges();

	new window.MutationObserver( insertBadges ).observe( document.body, {
		childList: true,
		subtree: true,
	} );
} )();
