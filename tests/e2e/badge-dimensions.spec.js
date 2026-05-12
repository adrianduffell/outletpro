import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import badgeDimensions from './fixtures/badge-dimensions.js';

/**
 * Returns the active theme's stylesheet slug via the WordPress REST API.
 *
 * @param {Object} requestUtils - Playwright REST request utilities.
 * @return {Promise<string>} The active theme slug.
 */
async function getActiveThemeSlug( requestUtils ) {
	const [ activeTheme ] = await requestUtils.rest( {
		method: 'GET',
		path: '/wp/v2/themes',
		params: { status: 'active' },
	} );
	return activeTheme.stylesheet;
}

test( 'badge has correct font-size and padding on single product page', async ( {
	page,
	admin,
	requestUtils,
} ) => {
	const themeSlug = await getActiveThemeSlug( requestUtils );
	const fixture = badgeDimensions[ themeSlug ];
	test.skip(
		! fixture,
		`No badge dimension fixture for theme: ${ themeSlug }`
	);

	// Arrange.
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

	const actualFontSize = await badge.evaluate(
		( el ) => window.getComputedStyle( el ).fontSize
	);
	const actualPaddingTop = await badge.evaluate(
		( el ) => window.getComputedStyle( el ).paddingTop
	);
	// eslint-disable-next-line no-console
	console.log(
		`[badge-dimensions] theme: ${ themeSlug }, product page — font-size: ${ actualFontSize }, padding-top: ${ actualPaddingTop }`
	);

	test.fail();
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
	const themeSlug = await getActiveThemeSlug( requestUtils );
	const fixture = badgeDimensions[ themeSlug ];
	test.skip(
		! fixture,
		`No badge dimension fixture for theme: ${ themeSlug }`
	);

	// Arrange.
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

	// Act: navigate to the product page, then add to cart via Store API.
	const productData = await requestUtils.rest( {
		method: 'GET',
		path: `/wc/v3/products/${ product.id }`,
	} );
	await page.goto( productData.permalink );
	const nonce = await page.evaluate( () => window.wcStoreApiNonce );
	await page.request.post( '/wp-json/wc/store/v1/cart/add-item', {
		headers: {
			'Content-Type': 'application/json',
			'X-WC-Store-API-Nonce': nonce,
		},
		data: JSON.stringify( { id: product.id, quantity: 1 } ),
	} );

	await page.goto( '/cart/' );

	// Assert.
	// The badge is rendered as CSS generated content on the cart page (see cart.css).
	// Block cart: ::before on .wc-block-components-product-metadata
	// Shortcode cart: ::after on td.product-name
	const blockBadgeHost = page.locator(
		'.wc-block-cart-item__product:has(.wc-clearance-cart-item-meta) .wc-block-components-product-metadata'
	);
	const isBlockCart = ( await blockBadgeHost.count() ) > 0;
	const badgeHost = isBlockCart
		? blockBadgeHost.first()
		: page
				.locator(
					'.shop_table td.product-name:has(.wc-clearance-cart-item-meta)'
				)
				.first();
	const pseudoElement = isBlockCart ? '::before' : '::after';

	await expect( badgeHost ).toBeVisible();

	const actualFontSize = await badgeHost.evaluate(
		( el, pseudo ) => window.getComputedStyle( el, pseudo ).fontSize,
		pseudoElement
	);
	const actualPaddingTop = await badgeHost.evaluate(
		( el, pseudo ) => window.getComputedStyle( el, pseudo ).paddingTop,
		pseudoElement
	);
	// eslint-disable-next-line no-console
	console.log(
		`[badge-dimensions] theme: ${ themeSlug }, cart page — font-size: ${ actualFontSize }, padding-top: ${ actualPaddingTop }`
	);

	test.fail();
	expect( actualFontSize ).toBe( fixture.cartPage.fontSize );
	expect( actualPaddingTop ).toBe( fixture.cartPage.padding );
} );
