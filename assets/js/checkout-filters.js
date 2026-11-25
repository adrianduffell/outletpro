/**
 * Checkout filters for storefront carts and editor previews.
 *
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

( () => {
	const { registerCheckoutFilters } = window.wc.blocksCheckout;

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
} )();
