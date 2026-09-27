/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import badgeDimensions from '../../fixtures/badge-dimensions.json' with { type: 'json' };

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

/**
 * Returns the current viewport as a `{width}x{height}` string.
 *
 * @param {import('@playwright/test').Page} page - The Playwright page object.
 * @return {string} Viewport key, e.g. `'1280x720'`.
 */
function getViewportKey( page ) {
	const { width, height } = page.viewportSize();
	return `${ width }x${ height }`;
}

/**
 * Opens the mini-cart drawer and returns the badge locator. Returns null when
 * no mini-cart button is present.
 *
 * @param {import('@playwright/test').Page} page
 * @return {Promise<import('@playwright/test').Locator|null>} Badge locator, or null when no mini-cart button is present.
 */
async function getMiniCartBadge( page ) {
	const miniCartButton = page.locator( '.wc-block-mini-cart__button' );
	if ( ( await miniCartButton.count() ) === 0 ) {
		return null;
	}
	await miniCartButton.click();
	const locator = page
		.locator( '.wc-block-mini-cart__drawer .outletpro-badge' )
		.first();
	return locator;
}

/**
 * Returns the cart badge locator for the cart page.
 * Detects block vs shortcode cart automatically.
 *
 * @param {import('@playwright/test').Page} page
 * @return {Promise<import('@playwright/test').Locator>} Badge locator.
 */
async function getCartBadge( page ) {
	const isBlock =
		( await page.locator( '.wp-block-woocommerce-cart' ).count() ) > 0;
	const locator = isBlock
		? page.locator( '.outletpro-cart-item .outletpro-badge' ).first()
		: page.locator( '.shop_table .outletpro-badge' ).first();
	return locator;
}

/**
 * Returns the checkout badge locator for the checkout page.
 * Detects block vs shortcode checkout automatically.
 *
 * @param {import('@playwright/test').Page} page
 * @return {Promise<import('@playwright/test').Locator>} Badge locator.
 */
async function getCheckoutBadge( page ) {
	const isBlock =
		( await page.locator( '.wp-block-woocommerce-checkout' ).count() ) > 0;
	const locator = isBlock
		? page.locator( '.outletpro-cart-item .outletpro-badge' ).first()
		: page.locator( '.shop_table .outletpro-badge' ).first();
	return locator;
}

/**
 * Fills a WooCommerce checkout form (block or classic shortcode).
 *
 * Detects which checkout variant is present and fills the billing fields
 * using the accessibility API (labels) so the helper works regardless of
 * the active checkout implementation.
 *
 * @param {import('@playwright/test').Page} checkoutPage
 */
async function fillCheckout( checkoutPage ) {
	const isBlock =
		( await checkoutPage
			.locator( '.wp-block-woocommerce-checkout' )
			.count() ) > 0;

	if ( isBlock ) {
		await checkoutPage
			.getByLabel( 'Email address' )
			.fill( 'test@example.com' );
		await checkoutPage.getByLabel( 'First name' ).fill( 'Test' );
		await checkoutPage.getByLabel( 'Last name' ).fill( 'Customer' );
		await checkoutPage.getByLabel( /country/i ).selectOption( 'US' );
		// Use .first() to target Address line 1, skipping the optional line 2.
		await checkoutPage
			.getByLabel( /^address/i )
			.first()
			.fill( '123 Test Street' );
		await checkoutPage.getByLabel( 'City' ).fill( 'Test City' );
		await checkoutPage.getByLabel( /zip|postal/i ).fill( '10001' );
		await checkoutPage.getByLabel( /^state/i ).selectOption( 'NY' );
		// Phone is optional in block checkout — only fill if the field is present.
		const blockPhone = checkoutPage.getByLabel( /phone/i );
		if ( ( await blockPhone.count() ) > 0 ) {
			await blockPhone.first().fill( '1234567890' );
		}
	} else {
		// Classic shortcode checkout.
		await checkoutPage
			.getByLabel( /email address/i )
			.fill( 'test@example.com' );
		await checkoutPage
			.getByLabel( /first name/i )
			.first()
			.fill( 'Test' );
		await checkoutPage
			.getByLabel( /last name/i )
			.first()
			.fill( 'Customer' );
		await checkoutPage
			.getByLabel( /country/i )
			.first()
			.selectOption( 'US' );
		// Use .first() to target "Street address" line 1, skipping the optional line 2.
		await checkoutPage
			.getByLabel( /street address/i )
			.first()
			.fill( '123 Test Street' );
		await checkoutPage
			.getByLabel( /town|city/i )
			.first()
			.fill( 'Test City' );
		await checkoutPage
			.getByLabel( /zip|postcode/i )
			.first()
			.fill( '10001' );
		await checkoutPage.getByLabel( /state/i ).first().selectOption( 'NY' );
		await checkoutPage.getByLabel( /phone/i ).first().fill( '1234567890' );
	}
}

/**
 * Places an order from the checkout page.
 *
 * Waits until the checkout is ready before clicking the Place order button.
 *
 * @param {import('@playwright/test').Page} page
 */
async function placeOrder( page ) {
	const button = page.getByRole( 'button', {
		name: /place order/i,
	} );

	await expect( button ).toBeEnabled();
	await button.click();
}

test( 'Shopping flow', async ( { page, admin, requestUtils, browser } ) => {
	// Arrange.
	const runId = Date.now();
	const themeSlug = await getActiveThemeSlug( requestUtils );

	const product = await requestUtils.rest( {
		method: 'POST',
		path: '/wc/v3/products',
		data: {
			name: `Order Flow Test Product ${ runId }`,
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
	await page.getByRole( 'checkbox', { name: 'Outlet' } ).check();
	await page.getByRole( 'button', { name: 'Update' } ).click();

	const productData = await requestUtils.rest( {
		method: 'GET',
		path: `/wc/v3/products/${ product.id }`,
	} );

	const wpSettings = await requestUtils.rest( {
		method: 'GET',
		path: '/wp/v2/settings',
	} );
	await requestUtils.rest( {
		method: 'PUT',
		path: `/wp/v2/pages/${ wpSettings.outletpro_page_id }`,
		data: { status: 'publish' },
	} );
	const outletPage = await requestUtils.rest( {
		method: 'GET',
		path: `/wp/v2/pages/${ wpSettings.outletpro_page_id }`,
	} );

	// Customer flow in isolated context.
	const customerContext = await browser.newContext( {
		storageState: { cookies: [], origins: [] },
	} );
	const customerPage = await customerContext.newPage();

	const viewportKey = getViewportKey( customerPage );
	const fixture = badgeDimensions?.[ themeSlug ]?.[ viewportKey ];

	// Open the outlet page.
	await customerPage.goto( outletPage.link );

	await expect( customerPage.locator( '#wpadminbar' ) ).toHaveCount( 0 );

	// Add an outlet product to the cart.
	await customerPage
		.getByRole( 'button', { name: /add to cart/i } )
		.first()
		.click();

	// Wait for the cart to update.
	await expect
		.poll( async () => {
			const res = await customerPage.request.get(
				'/?rest_route=/wc/store/v1/cart/items'
			);

			const items = await res.json();

			return items.length;
		} )
		.toBeGreaterThan( 0 );

	// Navigate to the product page and check badge dimensions.
	await customerPage.goto( productData.permalink );
	const badge = customerPage.locator( 'main .outletpro-badge' );
	await expect( badge ).toBeVisible();
	await expect
		.soft( badge, 'Product font-size' )
		.toHaveCSS( 'font-size', fixture?.productPage?.fontSize );
	await expect
		.soft( badge, 'Product padding' )
		.toHaveCSS( 'padding-top', fixture?.productPage?.padding );

	// Check badge in the mini-cart (block themes only).
	// The mini-cart drawer uses the same cart-item DOM structure as the cart block.
	const miniCartBadge = await getMiniCartBadge( customerPage );
	if ( miniCartBadge ) {
		await expect( miniCartBadge ).toBeVisible();
		await expect
			.soft( miniCartBadge, 'Minicart font-size' )
			.toHaveCSS( 'font-size', fixture?.cartPage?.fontSize );
		await expect
			.soft( miniCartBadge, 'Minicart padding' )
			.toHaveCSS( 'padding-top', fixture?.cartPage?.padding );
		await customerPage
			.locator( '.wc-block-mini-cart__drawer' )
			.getByLabel( 'Close' )
			.first()
			.click();
	}

	// Navigate to the cart page and check badge dimensions.
	// Click the checkout link in the menu.
	await customerPage
		.locator( 'nav' )
		.getByRole( 'link', { name: /^cart$/i } )
		.first()
		.click();
	const cartBadgeHost = await getCartBadge( customerPage );
	await expect( cartBadgeHost ).toBeVisible();
	await expect
		.soft( cartBadgeHost, 'Cart font-size' )
		.toHaveCSS( 'font-size', fixture?.cartPage?.fontSize );
	await expect
		.soft( cartBadgeHost, 'Cart padding' )
		.toHaveCSS( 'padding-top', fixture?.cartPage?.padding );

	// Click the checkout link in the menu.
	await customerPage
		.locator( 'nav' )
		.getByRole( 'link', { name: /^checkout$/i } )
		.first()
		.click();

	// Wait for visible email field.
	await customerPage
		.getByLabel( /email address|billing email/i )
		.waitFor( { state: 'visible' } );

	// Check badge in the checkout order summary.
	const checkoutBadgeHost = await getCheckoutBadge( customerPage );

	await expect( checkoutBadgeHost ).toBeVisible();
	await expect
		.soft( checkoutBadgeHost, 'Checkout font-size' )
		.toHaveCSS( 'font-size', fixture?.checkoutPage?.fontSize );
	await expect
		.soft( checkoutBadgeHost, 'Checkout padding' )
		.toHaveCSS( 'padding-top', fixture?.checkoutPage?.padding );

	await fillCheckout( customerPage );
	await placeOrder( customerPage );

	// Wait for the order page.
	const orderId = (
		await customerPage
			// Match block or classic confirmation page.
			.locator(
				`
				.woocommerce-order-overview__order strong,
				.wc-block-order-confirmation-summary-list-item:has(.wc-block-order-confirmation-summary-list-item__key:text("Order"))
					.wc-block-order-confirmation-summary-list-item__value
				`
			)
			.first()
			.textContent()
	)?.trim();

	expect( orderId ).toMatch( /^\d+$/ );

	await customerContext.close();
} );
