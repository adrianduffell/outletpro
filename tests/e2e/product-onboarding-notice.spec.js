/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

/**
 * Deletes all products via the WC REST API.
 *
 * Used to ensure no outlet products exist before tests that rely on the
 * onboarding notice being visible.
 *
 * @param {Object} requestUtils - Playwright request utilities.
 */
async function deleteAllProducts( requestUtils ) {
	const products = await requestUtils.rest( {
		method: 'GET',
		path: '/wc/v3/products',
		params: {
			per_page: 100,
		},
	} );
	for ( const product of products ) {
		await requestUtils.rest( {
			method: 'DELETE',
			path: `/wc/v3/products/${ product.id }`,
		} );
	}
}

test( 'notice shows when there are no outlet products', async ( {
	page,
	admin,
	requestUtils,
} ) => {
	// Arrange: delete all products so no outlet products exist.
	await deleteAllProducts( requestUtils );

	// Act.
	await admin.visitAdminPage( 'edit.php', 'post_type=product' );

	// Assert.
	await expect(
		page.locator( '.outletpro-onboarding-notice' )
	).toBeVisible();
} );

test( 'notice still shows when an outlet product exists but no page is configured', async ( {
	page,
	admin,
	requestUtils,
} ) => {
	// Arrange: create a product and add it to the store’s outlet via the admin UI.
	const product = await requestUtils.rest( {
		method: 'POST',
		path: '/wc/v3/products',
		data: {
			name: 'Test Outlet Product',
			type: 'simple',
			status: 'publish',
		},
	} );

	await admin.visitAdminPage(
		'post.php',
		`post=${ product.id }&action=edit`
	);
	await page.getByRole( 'link', { name: 'Inventory' } ).click();
	await page.getByRole( 'checkbox', { name: 'Outlet' } ).check();
	await page.getByRole( 'button', { name: 'Update' } ).click();
	await page.waitForLoadState( 'networkidle' );

	// Act.
	await admin.visitAdminPage( 'edit.php', 'post_type=product' );

	// Assert: the notice is still shown in the "products added" state.
	await expect(
		page.locator( '.outletpro-onboarding-notice' )
	).toBeVisible();

	// Cleanup.
	await requestUtils.rest( {
		method: 'DELETE',
		path: `/wc/v3/products/${ product.id }`,
		data: { force: true },
	} );
} );

test( 'notice does not show when localStorage is unavailable', async ( {
	page,
	admin,
} ) => {
	// Arrange: simulate a localStorage access failure (e.g. privacy mode).
	await page.addInitScript( () => {
		Object.defineProperty( window, 'localStorage', {
			get() {
				throw new Error( 'Simulated localStorage failure' );
			},
		} );
	} );

	// Act.
	await admin.visitAdminPage( 'edit.php', 'post_type=product' );

	// Assert: notice stays hidden because localStorage is inaccessible.
	await expect(
		page.locator( '.outletpro-onboarding-notice' )
	).not.toBeVisible();
} );

test( 'notice does not show again after being dismissed', async ( {
	page,
	admin,
	requestUtils,
} ) => {
	// Arrange: delete all products so no outlet products exist.
	await deleteAllProducts( requestUtils );

	// Arrange: visit the product list and confirm the notice is visible.
	await admin.visitAdminPage( 'edit.php', 'post_type=product' );
	await expect(
		page.locator( '.outletpro-onboarding-notice' )
	).toBeVisible();

	// Act: dismiss the notice.
	await page
		.locator( '.outletpro-onboarding-notice .notice-dismiss' )
		.click();

	// Navigate away and back.
	await admin.visitAdminPage( 'edit.php', 'post_type=product' );

	// Assert: notice is hidden because the dismissal key is in localStorage.
	await expect(
		page.locator( '.outletpro-onboarding-notice' )
	).not.toBeVisible();
} );

test( '"New" badge in notice title has light purple background and dark purple text', async ( {
	page,
	admin,
	requestUtils,
} ) => {
	// Arrange: delete all products so the notice appears.
	await deleteAllProducts( requestUtils );

	// Act.
	await admin.visitAdminPage( 'edit.php', 'post_type=product' );

	// Assert.
	const newBadge = page.locator( '.outletpro-new' );
	await expect( newBadge ).toBeVisible();
	await expect( newBadge ).toHaveCSS(
		'background-color',
		'rgb(242, 237, 255)'
	);
	await expect( newBadge ).toHaveCSS( 'color', 'rgb(44, 4, 93)' );
} );
