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

test( 'customer places clearance order', async ( {
	page,
	admin,
	requestUtils,
	browser,
} ) => {
	const runId = Date.now();
	const themeSlug = await getActiveThemeSlug( requestUtils );
	const viewportKey = getViewportKey( page );
	const fixture = badgeDimensions?.[ themeSlug ]?.[ viewportKey ];
	const hasFixture =
		fixture?.productPage && fixture?.cartPage && fixture?.checkoutPage;

	test.skip(
		! hasFixture,
		`No badge dimensions fixture for theme "${ themeSlug }" at viewport "${ viewportKey }".`
	);

	const { productData, clearancePage } =
		await test.step( 'Arrange product and clearance page', async () => {
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
			await page
				.getByRole( 'checkbox', { name: 'Clearance section' } )
				.check();
			await page.getByRole( 'button', { name: 'Update' } ).click();

			const resolvedProductData = await requestUtils.rest( {
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
			const resolvedClearancePage = await requestUtils.rest( {
				method: 'GET',
				path: `/wp/v2/pages/${ wpSettings.wc_clearance_page_id }`,
			} );

			return {
				productData: resolvedProductData,
				clearancePage: resolvedClearancePage,
			};
		} );

	const { customerContext, customerPage } =
		await test.step( 'Arrange customer context', async () => {
			const context = await browser.newContext( {
				storageState: { cookies: [], origins: [] },
			} );
			const newCustomerPage = await context.newPage();
			return { customerContext: context, customerPage: newCustomerPage };
		} );
	await test.step( 'Shop from clearance page and verify product badges', async () => {
		await customerPage.goto( clearancePage.link );

		await expect( customerPage.locator( '#wpadminbar' ) ).toHaveCount( 0 );

		await customerPage
			.getByRole( 'button', { name: /add to cart/i } )
			.first()
			.click();

		await expect
			.poll( async () => {
				const res = await customerPage.request.get(
					'/?rest_route=/wc/store/v1/cart/items'
				);

				const items = await res.json();

				return items.length;
			} )
			.toBeGreaterThan( 0 );

		await customerPage.goto( productData.permalink );
		const badge = customerPage.locator( '.wc-clearance-badge' );
		await expect( badge ).toBeVisible();
		await expect
			.soft( badge )
			.toHaveCSS( 'font-size', fixture.productPage.fontSize );
		await expect
			.soft( badge )
			.toHaveCSS( 'padding-top', fixture.productPage.padding );

		const miniCartBadge = await getMiniCartBadge( customerPage );
		if ( miniCartBadge ) {
			await checkPseudoBadgeDimensions(
				miniCartBadge.locator,
				miniCartBadge.pseudo,
				fixture.cartPage
			);
			await customerPage
				.locator( '.wc-block-mini-cart__drawer' )
				.getByLabel( 'Close' )
				.first()
				.click();
		}
	} );

	await test.step( 'Verify cart and checkout badge dimensions', async () => {
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
			fixture.cartPage
		);

		await customerPage
			.locator( 'nav' )
			.getByRole( 'link', { name: /^checkout$/i } )
			.first()
			.click();

		await customerPage
			.getByLabel( /email address|billing email/i )
			.waitFor( { state: 'visible' } );

		const { locator: checkoutBadgeHost, pseudo: checkoutPseudo } =
			await getCheckoutBadge( customerPage );

		await checkPseudoBadgeDimensions(
			checkoutBadgeHost,
			checkoutPseudo,
			fixture.checkoutPage
		);
	} );

	await test.step( 'Place order', async () => {
		await fillCheckout( customerPage );
		await customerPage
			.getByRole( 'button', { name: /place order/i } )
			.click();
		const orderSelector =
			'.woocommerce-order-overview__order strong, .wc-block-order-confirmation-summary-list-item:has(.wc-block-order-confirmation-summary-list-item__key:text("Order")) .wc-block-order-confirmation-summary-list-item__value';

		const orderId = (
			await customerPage.locator( orderSelector ).first().textContent()
		)?.trim();

		expect( orderId ).toMatch( /^\d+$/ );
	} );

	await test.step( 'Close customer context', async () => {
		await customerContext.close();
	} );
} );
