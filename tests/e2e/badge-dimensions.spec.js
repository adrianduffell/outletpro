import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import badgeDimensionFixtures from './fixtures/badge-dimensions.js';

const themeSlug = process.env.THEME;

test( 'badge has correct font-size and padding on single product page', async ( {
	page,
	admin,
	requestUtils,
} ) => {
	// Arrange.
	const fixture = themeSlug ? badgeDimensionFixtures[ themeSlug ] : undefined;
	test.skip(
		! fixture,
		`No badge dimension fixture for theme: ${ themeSlug }`
	);

	const product = await requestUtils.rest( {
		method: 'POST',
		path: '/wc/v3/products',
		data: {
			name: 'Badge Dimensions Product Page Test',
			type: 'simple',
			status: 'publish',
			regular_price: '9.99',
		},
	} );

	await admin.visitAdminPage(
		'post.php',
		`post=${ product.id }&action=edit`
	);
	await page.getByRole( 'link', { name: 'Inventory' } ).click();
	await page.getByRole( 'checkbox', { name: 'Clearance section' } ).check();
	await page.getByRole( 'button', { name: 'Update' } ).click();
	await page.waitForLoadState( 'networkidle' );

	// Act: navigate to the product's front-end page.
	const productData = await requestUtils.rest( {
		method: 'GET',
		path: `/wc/v3/products/${ product.id }`,
	} );
	await page.goto( productData.permalink );

	// Assert.
	const badge = page.locator( '.wc-clearance-badge' );
	await expect( badge ).toBeVisible();
	await expect( badge ).toHaveCSS(
		'font-size',
		fixture.productPage.fontSize
	);
	await expect( badge ).toHaveCSS(
		'padding-top',
		fixture.productPage.padding
	);
} );

test( 'badge has correct font-size and padding on cart page', async ( {
	page,
	admin,
	requestUtils,
} ) => {
	// Arrange.
	const fixture = themeSlug ? badgeDimensionFixtures[ themeSlug ] : undefined;
	test.skip(
		! fixture,
		`No badge dimension fixture for theme: ${ themeSlug }`
	);

	const product = await requestUtils.rest( {
		method: 'POST',
		path: '/wc/v3/products',
		data: {
			name: 'Badge Dimensions Cart Test',
			type: 'simple',
			status: 'publish',
			regular_price: '9.99',
		},
	} );

	await admin.visitAdminPage(
		'post.php',
		`post=${ product.id }&action=edit`
	);
	await page.getByRole( 'link', { name: 'Inventory' } ).click();
	await page.getByRole( 'checkbox', { name: 'Clearance section' } ).check();
	await page.getByRole( 'button', { name: 'Update' } ).click();
	await page.waitForLoadState( 'networkidle' );

	// Act: navigate to the product page and add it to the cart.
	const productData = await requestUtils.rest( {
		method: 'GET',
		path: `/wc/v3/products/${ product.id }`,
	} );
	await page.goto( productData.permalink );
	await page.getByRole( 'button', { name: /add to cart/i } ).click();

	// Wait for the cart to be updated before navigating to the cart page.
	await expect
		.poll( async () => {
			const res = await page.request.get(
				'/?rest_route=/wc/store/v1/cart/items'
			);
			const items = await res.json();
			return items.length;
		} )
		.toBeGreaterThan( 0 );

	await page.goto( '/cart/' );

	// Assert.
	const badge = page.locator( '.wc-clearance-badge' );
	await expect( badge ).toBeVisible();
	await expect( badge ).toHaveCSS( 'font-size', fixture.cartPage.fontSize );
	await expect( badge ).toHaveCSS( 'padding-top', fixture.cartPage.padding );
} );
