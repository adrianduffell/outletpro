import { expect } from '@wordpress/e2e-test-utils-playwright';

/**
 * Creates a clearance product and ensures the clearance page is published.
 *
 * @param {Object}                          args
 * @param {import('@playwright/test').Page} args.page
 * @param {Object}                          args.admin
 * @param {Object}                          args.requestUtils
 * @return {Promise<{clearancePage: Object, product: Object, productData: Object}>} Created product and clearance page data.
 */
export async function createClearanceOrderProduct( {
	page,
	admin,
	requestUtils,
} ) {
	const runId = Date.now();
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

	return { clearancePage, product, productData };
}

/**
 * Creates an isolated browser context for the storefront customer flow.
 *
 * @param {import('@playwright/test').Browser} browser
 * @return {Promise<{customerContext: import('@playwright/test').BrowserContext, customerPage: import('@playwright/test').Page}>} Customer context and page.
 */
export async function createCustomerPage( browser ) {
	const customerContext = await browser.newContext( {
		storageState: { cookies: [], origins: [] },
	} );
	const customerPage = await customerContext.newPage();

	return { customerContext, customerPage };
}

/**
 * Adds the first clearance product on the clearance page to the cart.
 *
 * @param {Object}                          args
 * @param {import('@playwright/test').Page} args.customerPage
 * @param {Object}                          args.clearancePage
 */
export async function addClearanceProductToCart( {
	customerPage,
	clearancePage,
} ) {
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
export async function fillCheckout( checkoutPage ) {
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
		await checkoutPage
			.getByLabel( /^address/i )
			.first()
			.fill( '123 Test Street' );
		await checkoutPage.getByLabel( 'City' ).fill( 'Test City' );
		await checkoutPage.getByLabel( /zip|postal/i ).fill( '10001' );
		await checkoutPage.getByLabel( /^state/i ).selectOption( 'NY' );

		const blockPhone = checkoutPage.getByLabel( /phone/i );
		if ( ( await blockPhone.count() ) > 0 ) {
			await blockPhone.first().fill( '1234567890' );
		}
	} else {
		await checkoutPage
			.getByLabel( /email address/i )
			.fill( 'test@example.com' );
		await checkoutPage.getByLabel( /first name/i ).fill( 'Test' );
		await checkoutPage.getByLabel( /last name/i ).fill( 'Customer' );
		await checkoutPage
			.getByLabel( /country/i )
			.first()
			.selectOption( 'US' );
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

/**
 * Places an order for the current customer cart contents.
 *
 * @param {import('@playwright/test').Page}                            page
 * @param {Object}                                                     [callbacks]
 * @param {( page: import('@playwright/test').Page ) => Promise<void>} [callbacks.onCartPage]
 * @param {( page: import('@playwright/test').Page ) => Promise<void>} [callbacks.onCheckoutPage]
 * @return {Promise<string>} Placed order ID.
 */
export async function placeOrder( page, callbacks = {} ) {
	const { onCartPage, onCheckoutPage } = callbacks;

	await page
		.locator( 'nav' )
		.getByRole( 'link', { name: /^cart$/i } )
		.first()
		.click();

	if ( onCartPage ) {
		await onCartPage( page );
	}

	await page
		.locator( 'nav' )
		.getByRole( 'link', { name: /^checkout$/i } )
		.first()
		.click();

	await page
		.getByLabel( /email address|billing email/i )
		.waitFor( { state: 'visible' } );

	if ( onCheckoutPage ) {
		await onCheckoutPage( page );
	}

	await fillCheckout( page );

	await page.getByRole( 'button', { name: /place order/i } ).click();

	const orderId = (
		await page
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

	return orderId;
}
