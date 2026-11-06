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

	const insertPreviewBadges = () => {
		const label =
			document.querySelector( '#outletpro-preview-vars' )?.dataset
				.outletproBadgeLabel ?? '';

		for ( const row of document.querySelectorAll(
			'.outletpro-cart-item'
		) ) {
			const existingBadge = row.querySelector( '.outletpro-badge' );

			if ( '' === label.trim() ) {
				existingBadge?.parentElement.remove();
				continue;
			}

			if ( existingBadge ) {
				if ( existingBadge.textContent !== label ) {
					existingBadge.textContent = label;
				}
				continue;
			}

			const anchor = row.querySelector(
				'.wc-block-components-product-name'
			);

			if ( ! anchor ) {
				continue;
			}

			const badge = document.createElement( 'div' );
			badge.className = 'outletpro-badge';
			badge.textContent = label;

			const badgeContainer = document.createElement( 'div' );
			badgeContainer.append( badge );
			anchor.before( badgeContainer );
		}
	};

	insertPreviewBadges();

	new window.MutationObserver( insertPreviewBadges ).observe( document, {
		childList: true,
		subtree: true,
		attributes: true,
		attributeFilter: [ 'data-outletpro-badge-label' ],
	} );
} )();
