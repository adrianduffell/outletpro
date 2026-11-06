/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

( () => {
	const { registerCheckoutFilters } = window.wc.blocksCheckout;

	const modifyCartItemClassEditor = ( defaultValue, extensions, args ) => {
		if ( extensions?.outletpro !== undefined ) {
			return defaultValue;
		}

		if ( args.cartItem.key !== args.cart.cartItems[ 0 ].key ) {
			return defaultValue;
		}

		return ( defaultValue + ' outletpro-cart-item' ).trim();
	};

	registerCheckoutFilters( 'outletpro-editor', {
		cartItemClass: modifyCartItemClassEditor,
	} );
} )();
