/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

const licenseKey = process.env.OUTLETPRO_LICENSE_KEY;

test.describe.configure( { mode: 'serial' } );

test(
	'show the welcome page navigation menu item',
	{ tag: '@premium-license' },
	async ( { page, admin, requestUtils } ) => {
		// Arrange.
		await requestUtils.rest( {
			path: '/wp/v2/settings',
			method: 'POST',
			data: {
				outletpro_license_key: '',
			},
		} );

		// Act.
		await admin.visitAdminPage( 'index.php' );

		// Assert.
		await expect(
			page.getByRole( 'link', { name: 'Outlet Pro Setup' } )
		).toBeVisible();
	}
);

test(
	'add a license key from the welcome page',
	{ tag: '@premium-license' },
	async ( { page, admin, requestUtils } ) => {
		test.skip(
			! licenseKey,
			'OUTLETPRO_LICENSE_KEY environment variable not found.'
		);

		// Arrange.
		await requestUtils.rest( {
			path: '/wp/v2/settings',
			method: 'POST',
			data: {
				outletpro_license_key: '',
			},
		} );
		await admin.visitAdminPage( 'admin.php', 'page=outletpro-welcome' );

		// Act.
		await page
			.getByRole( 'textbox', { name: 'Premium license key' } )
			.fill( licenseKey );
		await expect(
			page.getByRole( 'button', { name: 'Activate license' } )
		).toBeEnabled();
		await page.getByRole( 'button', { name: 'Activate license' } ).click();

		// Assert.
		await expect(
			page.getByRole( 'heading', { name: '🎉 Success!' } )
		).toBeVisible();
	}
);

test(
	'hide the welcome page navigation menu item',
	{ tag: '@premium-license' },
	async ( { page, admin } ) => {
		test.skip(
			! licenseKey,
			'OUTLETPRO_LICENSE_KEY environment variable not found.'
		);

		// Act.
		await admin.visitAdminPage( 'index.php' );

		// Assert.
		await expect(
			page.getByRole( 'link', { name: 'Outlet Pro Setup' } )
		).toHaveCount( 0 );
	}
);
