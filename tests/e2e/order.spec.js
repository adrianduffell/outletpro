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
 * @param {import('@playwright/test').Page} aPage - The Playwright page object.
 * @return {string} Viewport key, e.g. `'1280x720'`.
 */
function getViewportKey( aPage ) {
	const { width, height } = aPage.viewportSize();
	return `${ width }x${ height }`;
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

	// Navigate to the product page and check badge dimensions.
	await customerPage.goto( productData.permalink );
	const badge = customerPage.locator( '.wc-clearance-badge' );
	await expect.soft( badge ).toBeVisible();
	if ( await badge.isVisible() ) {
		const productFontSize = await badge.evaluate(
			( el ) => window.getComputedStyle( el ).fontSize
		);
		const productPaddingTop = await badge.evaluate(
			( el ) => window.getComputedStyle( el ).paddingTop
		);
		// eslint-disable-next-line no-console
		console.log(
			`[badge-dimensions] theme: ${ themeSlug }, viewport: ${ viewportKey }, product page — font-size: ${ productFontSize }, padding-top: ${ productPaddingTop }`
		);
		if ( fixture ) {
			await expect
				.soft( badge )
				.toHaveCSS( 'font-size', fixture.productPage.fontSize );
			await expect
				.soft( badge )
				.toHaveCSS( 'padding-top', fixture.productPage.padding );
		}
	}

	// Add the product to cart via Store API (avoids theme-specific UI).
	const nonce = await customerPage.evaluate( () => window.wcStoreApiNonce );
	await customerPage.request.post( '/wp-json/wc/store/v1/cart/add-item', {
		headers: {
			'Content-Type': 'application/json',
			'X-WC-Store-API-Nonce': nonce,
		},
		data: JSON.stringify( { id: product.id, quantity: 1 } ),
	} );

	// Navigate to the cart page and check badge dimensions.
	// The badge is rendered as CSS generated content on the cart page (see cart.css).
	// Block cart: ::before on .wc-block-components-product-metadata
	// Shortcode cart: ::after on td.product-name
	await customerPage.goto( '/cart/' );
	const blockBadgeHost = customerPage.locator(
		'.wc-block-cart-item__product:has(.wc-clearance-cart-item-meta) .wc-block-components-product-metadata'
	);
	const isBlockCart = ( await blockBadgeHost.count() ) > 0;
	const badgeHost = isBlockCart
		? blockBadgeHost.first()
		: customerPage
				.locator(
					'.shop_table td.product-name:has(.wc-clearance-cart-item-meta)'
				)
				.first();
	const pseudoElement = isBlockCart ? '::before' : '::after';
	await expect.soft( badgeHost ).toBeVisible();
	if ( await badgeHost.isVisible() ) {
		const cartFontSize = await badgeHost.evaluate(
			( el, pseudo ) => window.getComputedStyle( el, pseudo ).fontSize,
			pseudoElement
		);
		const cartPaddingTop = await badgeHost.evaluate(
			( el, pseudo ) => window.getComputedStyle( el, pseudo ).paddingTop,
			pseudoElement
		);
		// eslint-disable-next-line no-console
		console.log(
			`[badge-dimensions] theme: ${ themeSlug }, viewport: ${ viewportKey }, cart page — font-size: ${ cartFontSize }, padding-top: ${ cartPaddingTop }`
		);
		if ( fixture ) {
			expect.soft( cartFontSize ).toBe( fixture.cartPage.fontSize );
			expect.soft( cartPaddingTop ).toBe( fixture.cartPage.padding );
		}
	}

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
