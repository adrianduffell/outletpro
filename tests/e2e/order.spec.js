import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import badgeDimensions from '../../fixtures/badge-dimensions.json' with { type: 'json' };
import {
	addClearanceProductToCart,
	createClearanceOrderProduct,
	createCustomerPage,
	placeOrder,
} from './order-flow.js';

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
 * Reads computed badge dimensions from a CSS pseudo-element host and emits
 * soft assertions when fixture data is available.
 *
 * @param {import('@playwright/test').Locator}            locator     - Host element locator.
 * @param {string}                                        pseudo      - CSS pseudo-element string, e.g. `'::before'`.
 * @param {{fontSize: string, padding: string}|undefined} fixtureData - Expected values or undefined when no fixture matches.
 */
async function checkPseudoBadgeDimensions( locator, pseudo, fixtureData ) {
	await expect( locator ).toBeVisible();
	const { fontSize, paddingTop } = await locator.evaluate(
		( el, pseudoArg ) => {
			const style = window.getComputedStyle( el, pseudoArg );
			return { fontSize: style.fontSize, paddingTop: style.paddingTop };
		},
		pseudo
	);
	expect.soft( fontSize ).toBe( fixtureData?.fontSize );
	expect.soft( paddingTop ).toBe( fixtureData?.padding );
}

/**
 * Opens the mini-cart drawer and returns the badge host locator and
 * pseudo-element string. Returns null when no mini-cart button is present.
 *
 * @param {import('@playwright/test').Page} page
 * @return {Promise<{locator: import('@playwright/test').Locator, pseudo: string}|null>} Badge locator and pseudo-element, or null when no mini-cart button is present.
 */
async function getMiniCartBadge( page ) {
	const miniCartButton = page.locator( '.wc-block-mini-cart__button' );
	if ( ( await miniCartButton.count() ) === 0 ) {
		return null;
	}
	await miniCartButton.click();
	const locator = page
		.locator(
			'.wc-block-cart-item__product:has(.wc-clearance-cart-item-meta) .wc-block-components-product-metadata'
		)
		.first();
	return { locator, pseudo: '::before' };
}

/**
 * Returns the cart badge host locator and pseudo-element for the cart page.
 * Detects block vs shortcode cart automatically.
 *
 * @param {import('@playwright/test').Page} page
 * @return {Promise<{locator: import('@playwright/test').Locator, pseudo: string}>} Badge locator and pseudo-element string.
 */
async function getCartBadge( page ) {
	const isBlock =
		( await page.locator( '.wp-block-woocommerce-cart' ).count() ) > 0;
	const locator = isBlock
		? page
				.locator(
					'.wc-block-cart-item__product:has(.wc-clearance-cart-item-meta) .wc-block-components-product-metadata'
				)
				.first()
		: page
				.locator(
					'.shop_table td.product-name:has(.wc-clearance-cart-item-meta)'
				)
				.first();
	return { locator, pseudo: isBlock ? '::before' : '::after' };
}

/**
 * Returns the checkout badge host locator and pseudo-element for the checkout page.
 * Detects block vs shortcode checkout automatically.
 *
 * @param {import('@playwright/test').Page} page
 * @return {Promise<{locator: import('@playwright/test').Locator, pseudo: string}>} Badge locator and pseudo-element string.
 */
async function getCheckoutBadge( page ) {
	const isBlock =
		( await page.locator( '.wp-block-woocommerce-checkout' ).count() ) > 0;
	const locator = isBlock
		? page
				.locator(
					'.wc-block-components-order-summary-item__description:has(.wc-clearance-cart-item-meta) .wc-block-components-product-metadata'
				)
				.first()
		: page
				.locator(
					'.shop_table td.product-name:has(.wc-clearance-cart-item-meta)'
				)
				.first();
	return { locator, pseudo: isBlock ? '::before' : '::after' };
}

test( 'customer sees clearance badge throughout storefront order flow', async ( {
	page,
	admin,
	requestUtils,
	browser,
} ) => {
	// Arrange.
	const themeSlug = await getActiveThemeSlug( requestUtils );
	const { clearancePage, productData } = await createClearanceOrderProduct( {
		page,
		admin,
		requestUtils,
	} );
	const { customerContext, customerPage } =
		await createCustomerPage( browser );

	const viewportKey = getViewportKey( customerPage );
	const fixture = badgeDimensions?.[ themeSlug ]?.[ viewportKey ];

	await addClearanceProductToCart( { customerPage, clearancePage } );

	// Navigate to the product page and check badge dimensions.
	await customerPage.goto( productData.permalink );
	const badge = customerPage.locator( '.wc-clearance-badge' );
	await expect( badge ).toBeVisible();
	await expect
		.soft( badge )
		.toHaveCSS( 'font-size', fixture?.productPage?.fontSize );
	await expect
		.soft( badge )
		.toHaveCSS( 'padding-top', fixture?.productPage?.padding );

	// Check badge in the mini-cart (block themes only).
	// The mini-cart drawer uses the same cart-item DOM structure as the cart block.
	const miniCartBadge = await getMiniCartBadge( customerPage );
	if ( miniCartBadge ) {
		await checkPseudoBadgeDimensions(
			miniCartBadge.locator,
			miniCartBadge.pseudo,
			fixture?.cartPage
		);
		await customerPage
			.locator( '.wc-block-mini-cart__drawer' )
			.getByLabel( 'Close' )
			.first()
			.click();
	}

	await placeOrder( customerPage, {
		onCartPage: async ( checkoutPage ) => {
			const { locator: cartBadgeHost, pseudo: cartPseudo } =
				await getCartBadge( checkoutPage );

			await checkPseudoBadgeDimensions(
				cartBadgeHost,
				cartPseudo,
				fixture?.cartPage
			);
		},
		onCheckoutPage: async ( checkoutPage ) => {
			const { locator: checkoutBadgeHost, pseudo: checkoutPseudo } =
				await getCheckoutBadge( checkoutPage );

			await checkPseudoBadgeDimensions(
				checkoutBadgeHost,
				checkoutPseudo,
				fixture?.checkoutPage
			);
		},
	} );

	await customerContext.close();
} );
