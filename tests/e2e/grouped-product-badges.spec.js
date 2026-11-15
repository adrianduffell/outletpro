/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'grouped product badges identify only outlet children', async ( {
	page,
	admin,
	requestUtils,
} ) => {
	// Arrange.
	const runId = Date.now();
	const { create: children } = await requestUtils.rest( {
		method: 'POST',
		path: '/wc/v3/products/batch',
		data: {
			create: [
				{
					name: `Outlet child ${ runId }`,
					type: 'simple',
					regular_price: '20.00',
				},
				{
					name: `Regular child ${ runId }`,
					type: 'simple',
					regular_price: '30.00',
				},
			],
		},
	} );
	const parent = await requestUtils.rest( {
		method: 'POST',
		path: '/wc/v3/products',
		data: {
			name: `Grouped badge test ${ runId }`,
			type: 'grouped',
			grouped_products: children.map( ( child ) => child.id ),
		},
	} );
	await requestUtils.rest( {
		method: 'POST',
		path: '/wp/v2/settings',
		data: { outletpro_badge_label: 'Clearance' },
	} );
	for ( const product of [ parent, children[ 0 ] ] ) {
		await admin.visitAdminPage(
			'post.php',
			`post=${ product.id }&action=edit`
		);
		await page.getByRole( 'link', { name: 'Inventory' } ).click();
		await page
			.getByRole( 'checkbox', { name: 'Outlet', exact: true } )
			.check();
		await page
			.getByRole( 'button', { name: 'Update', exact: true } )
			.click();
		await expect( page.locator( '#message.notice-success' ) ).toContainText(
			'Product updated'
		);
	}

	// Act.
	await page.goto( parent.permalink );

	// Assert.
	const badge = page.getByRole( 'main' ).locator( '.outletpro-badge' );
	await expect( badge ).toHaveCount( 1 );
	await expect( badge ).toHaveText( 'Clearance' );
	await expect( badge ).toBeVisible();
	await expect( badge ).toHaveCSS( 'display', 'block' );
	await expect(
		page
			.locator(
				'.woocommerce-grouped-product-list-item__label, .wp-block-woocommerce-add-to-cart-with-options-grouped-product-item-label'
			)
			.filter( { hasText: children[ 1 ].name } )
	).toBeVisible();
	const childLabel = page
		.locator(
			'.woocommerce-grouped-product-list-item__label label, .wp-block-woocommerce-add-to-cart-with-options-grouped-product-item-label'
		)
		.filter( { hasText: children[ 0 ].name } );
	await expect( childLabel ).toBeVisible();
	await expect(
		childLabel.locator( 'xpath=following-sibling::*[1]' )
	).toContainText( 'Clearance' );
} );
