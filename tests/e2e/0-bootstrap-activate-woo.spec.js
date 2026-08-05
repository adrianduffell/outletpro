/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

// These tests need to run before any other WooCommerce bootstrapping.

test( 'activate woocommerce', async ( { requestUtils } ) => {
	await requestUtils.rest( {
		path: '/wp/v2/plugins/woocommerce/woocommerce',
		method: 'PUT',
		data: {
			status: 'active',
		},
	} );
} );

test( 'skip core profiler', async ( { page, admin } ) => {
	// Arrange.
	await admin.visitAdminPage(
		'admin.php',
		'page=wc-admin&path=/setup-wizard'
	);

	// Act.
	await page.getByRole( 'checkbox', { name: /share my data/i } ).uncheck();

	await page.getByRole( 'button', { name: /skip guided setup/i } ).click();

	await page.getByLabel( /Select country\/region/i ).fill( 'California' );

	await page
		.getByRole( 'option', { name: 'United States (US) — California' } )
		.click();

	await page.getByRole( 'button', { name: /Go to my store/i } ).click();

	// Assert.
	await expect(
		page.getByRole( 'heading', { name: /home/i } )
	).toBeVisible();
} );
