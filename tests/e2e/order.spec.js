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
 * Reads computed badge dimensions from a CSS pseudo-element host, logs them
 * to console, and emits soft assertions when fixture data is available.
 *
 * @param {import('@playwright/test').Locator}            locator     - Host element locator.
 * @param {string}                                        pseudo      - CSS pseudo-element string, e.g. `'::before'`.
 * @param {string}                                        label       - Human-readable label for console output.
 * @param {string}                                        themeSlug   - Active theme slug.
 * @param {string}                                        viewportKey - Viewport key string.
 * @param {{fontSize: string, padding: string}|undefined} fixtureData - Expected values or undefined when no fixture matches.
 */
async function checkPseudoBadgeDimensions(
	locator,
	pseudo,
	label,
	themeSlug,
	viewportKey,
	fixtureData
) {
	await expect( locator ).toBeVisible();
	const { fontSize, paddingTop } = await locator.evaluate(
		( el, pseudoArg ) => {
			const style = window.getComputedStyle( el, pseudoArg );
			return { fontSize: style.fontSize, paddingTop: style.paddingTop };
		},
		pseudo
	);
	// eslint-disable-next-line no-console
	console.log(
		`[badge-dimensions] theme: ${ themeSlug }, viewport: ${ viewportKey }, ${ label } — font-size: ${ fontSize }, padding-top: ${ paddingTop }`
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
	const blockHost = page.locator(
		'.wc-block-cart-item__product:has(.wc-clearance-cart-item-meta) .wc-block-components-product-metadata'
	);
	const isBlock = ( await blockHost.count() ) > 0;
	const locator = isBlock
		? blockHost.first()
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
		( await page.locator( 'body.woocommerce-uses-block-theme' ).count() ) >
		0;
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
		await checkoutPage.getByLabel( /first name/i ).fill( 'Test' );
		await checkoutPage.getByLabel( /last name/i ).fill( 'Customer' );
		await checkoutPage
			.getByLabel( /country/i )
			.first()
			.selectOption( 'US' );
		// Use .first() to target "Street address" line 1, skipping the optional line 2.
		await checkoutPage
			.getByLabel( /street address/i )
			.first()
			.fill( '123 Test Street' );
		await checkoutPage.getByLabel( /town|city/i ).fill( 'Test City' );
		await checkoutPage.getByLabel( /zip|postcode/i ).fill( '10001' );
		await checkoutPage.getByLabel( /state/i ).first().selectOption( 'NY' );
		await checkoutPage.getByLabel( /phone/i ).fill( '1234567890' );
	}
}

test( 'customer places clearance order and admin sees clearance badge on order', async ( {
	page,
	admin,
	requestUtils,
	browser,
} ) => {
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
	await page.getByRole( 'checkbox', { name: 'Clearance section' } ).check();
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
		path: `/wp/v2/pages/${ wpSettings.wc_clearance_page_id }`,
		data: { status: 'publish' },
	} );
	const clearancePage = await requestUtils.rest( {
		method: 'GET',
		path: `/wp/v2/pages/${ wpSettings.wc_clearance_page_id }`,
	} );

	// Customer flow in isolated context.
	const customerContext = await browser.newContext( {
		storageState: { cookies: [], origins: [] },
	} );
	const customerPage = await customerContext.newPage();

	const viewportKey = getViewportKey( customerPage );
	const fixture = badgeDimensions?.[ themeSlug ]?.[ viewportKey ];

	// Open the clearance page.
	await customerPage.goto( clearancePage.link );

	await expect( customerPage.locator( '#wpadminbar' ) ).toHaveCount( 0 );

	// Add a product in the clearance section to the cart.
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

	// Check badge in the mini-cart (block themes only).
	// The mini-cart drawer uses the same cart-item DOM structure as the cart block.
	const miniCartBadge = await getMiniCartBadge( customerPage );
	if ( miniCartBadge ) {
		await checkPseudoBadgeDimensions(
			miniCartBadge.locator,
			miniCartBadge.pseudo,
			'mini-cart',
			themeSlug,
			viewportKey,
			fixture?.cartPage
		);
		// Navigate away to close the mini-cart drawer before continuing.
	}

	// Navigate to the product page and check badge dimensions.
	await customerPage.goto( productData.permalink );
	const badge = customerPage.locator( '.wc-clearance-badge' );
	await expect( badge ).toBeVisible();
	const { fontSize: productFontSize, paddingTop: productPaddingTop } =
		await badge.evaluate( ( el ) => {
			const style = window.getComputedStyle( el );
			return { fontSize: style.fontSize, paddingTop: style.paddingTop };
		} );
	// eslint-disable-next-line no-console
	console.log(
		`[badge-dimensions] theme: ${ themeSlug }, viewport: ${ viewportKey }, product page — font-size: ${ productFontSize }, padding-top: ${ productPaddingTop }`
	);
	await expect
		.soft( badge )
		.toHaveCSS( 'font-size', fixture?.productPage?.fontSize );
	await expect
		.soft( badge )
		.toHaveCSS( 'padding-top', fixture?.productPage?.padding );

	// Navigate to the cart page and check badge dimensions.
	// The badge is rendered as CSS generated content on the cart page (see cart.css).
	// Block cart: ::before on .wc-block-components-product-metadata
	// Shortcode cart: ::after on td.product-name
	// Click the checkout link in the menu.
	await customerPage
		.locator( 'nav' )
		.getByRole( 'link', { name: /^cart$/i } )
		.first()
		.click();
	const { locator: cartBadgeHost, pseudo: cartPseudo } =
		await getCartBadge( customerPage );
	await checkPseudoBadgeDimensions(
		cartBadgeHost,
		cartPseudo,
		'cart page',
		themeSlug,
		viewportKey,
		fixture?.cartPage
	);

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
	// Block checkout: ::before on .wc-block-components-product-metadata inside .wc-block-components-order-summary-item__description
	// Shortcode checkout: ::after on .shop_table td.product-name (order review table)
	const { locator: checkoutBadgeHost, pseudo: checkoutPseudo } =
		await getCheckoutBadge( customerPage );
	await checkPseudoBadgeDimensions(
		checkoutBadgeHost,
		checkoutPseudo,
		'checkout',
		themeSlug,
		viewportKey,
		fixture?.checkoutPage
	);

	await fillCheckout( customerPage );

	await customerPage.getByRole( 'button', { name: /place order/i } ).click();

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

	// Assert.
	await admin.visitAdminPage(
		'admin.php',
		`page=wc-orders&action=edit&id=${ orderId }`
	);
	await expect( page.locator( '.wc-clearance-admin-badge' ) ).toBeVisible();
} );
