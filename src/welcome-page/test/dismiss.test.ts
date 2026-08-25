/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { dismiss, undoDismiss } from '../dismiss';

test( 'sets the permanent dismissal cookie', () => {
	// Arrange.
	document.cookie = 'OUTLETPRO_DISMISS_SETUP=; max-age=0; path=/';

	// Act.
	dismiss();

	// Assert.
	expect( document.cookie ).toContain( 'OUTLETPRO_DISMISS_SETUP=1' );
} );

test( 'removes the dismissal cookie', () => {
	// Arrange.
	document.cookie = 'OUTLETPRO_DISMISS_SETUP=1; path=/';

	// Act.
	undoDismiss();

	// Assert.
	expect( document.cookie ).not.toContain( 'OUTLETPRO_DISMISS_SETUP' );
} );
