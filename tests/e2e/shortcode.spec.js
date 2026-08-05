/**
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'shortcode shows outlet products on front end', async ( {
	page,
	admin,
	requestUtils,
} ) => {
	// Arrange: create 5 products with a unique run ID to avoid cross-run collisions.
	const runId = Date.now();
	const products = await Promise.all(
		[ 1, 2, 3, 4, 5 ].map( ( productNumber ) =>
			requestUtils.rest( {
				method: 'POST',
				path: '/wc/v3/products',
				data: {
					name: `Shortcode Test Product ${ productNumber } ${ runId }`,
					type: 'simple',
					status: 'publish',
				},
			} )
		)
	);
	const nonOutletProductName = products[ 2 ].name;

	for ( const product of [ products[ 0 ], products[ 1 ] ] ) {
		await admin.visitAdminPage(
			'post.php',
			`post=${ product.id }&action=edit`
		);
		await page.getByRole( 'link', { name: 'Inventory' } ).click();
		await page.getByRole( 'checkbox', { name: 'Outlet' } ).check();
		await page.getByRole( 'button', { name: 'Update' } ).click();
		await page.waitForLoadState( 'networkidle' );
	}

	// Act: create a page containing the [products outletpro="yes"] shortcode.
	const shortcodePage = await requestUtils.rest( {
		method: 'POST',
		path: '/wp/v2/pages',
		data: {
			title: `Shortcode Test Page ${ runId }`,
			content:
				'<!-- wp:shortcode -->[products outletpro="yes" limit="99" columns="4"]<!-- /wp:shortcode -->',
			status: 'publish',
		},
	} );

	await page.goto( shortcodePage.link );

	// Assert: outlet products shown on front end.
	await expect(
		page
			.getByRole( 'link', {
				name: `Shortcode Test Product 1 ${ runId }`,
			} )
			.first()
	).toBeVisible();
	await expect(
		page
			.getByRole( 'link', {
				name: `Shortcode Test Product 2 ${ runId }`,
			} )
			.first()
	).toBeVisible();
	await expect(
		page.getByRole( 'link', {
			name: nonOutletProductName,
		} )
	).toHaveCount( 0 );
	const frontEndRenderedProductCount = await page
		.getByRole( 'link', {
			name: new RegExp( `Shortcode Test Product \\d+ ${ runId }` ),
		} )
		.count();
	expect( frontEndRenderedProductCount ).toBeGreaterThanOrEqual( 1 );
	expect( frontEndRenderedProductCount ).toBeLessThanOrEqual( 99 );
} );
