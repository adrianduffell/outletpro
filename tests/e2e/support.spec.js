/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'Get support', { tag: '@premium-license' }, async ( { page, admin } ) => {
	// Act.
	await admin.visitAdminPage( 'plugins.php' );

	// Assert.
	const supportLink = page
		.locator( 'tr[data-slug="outlet-pro"]' )
		.getByRole( 'link', { name: 'Support' } );
	await expect( supportLink ).toBeVisible();
	await expect( supportLink ).toHaveAttribute( 'href', 'https://outletpro.zip/support' );
	await supportLink.click( { trial: true } );
