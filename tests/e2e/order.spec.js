import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'customer places clearance order and admin sees clearance badge on order', async ( {
	page,
	admin,
	requestUtils,
	browser,
} ) => {
	// Arrange.
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
	const customerContext = await browser.newContext();
	const customerPage = await customerContext.newPage();

	await customerPage.goto( clearancePage.link );

	// Assert the product is visible on the clearance page, then scope add-to-cart
	// to that specific product's card to avoid adding a different product.
	const productLink = customerPage.getByRole( 'link', {
		name: product.name,
		exact: true,
	} );
	await expect( productLink ).toBeVisible();

	const productCard = customerPage
		.locator( 'li.product, .wc-block-grid__product' )
		.filter( { has: productLink } )
		.first();

	const cartUpdatePromise = productCard
		.locator( '.added_to_cart' )
		.waitFor( { state: 'attached' } );

	await productCard.getByRole( 'button', { name: /add to cart/i } ).click();

	await cartUpdatePromise;

	await customerPage
		.getByRole( 'link', { name: /checkout/i } )
		.first()
		.click();

	await customerPage.locator( '#billing_email' ).fill( 'test@example.com' );
	await customerPage.locator( '#billing_first_name' ).fill( 'Test' );
	await customerPage.locator( '#billing_last_name' ).fill( 'Customer' );
	await customerPage.locator( '#billing_country' ).selectOption( 'US' );
	await customerPage
		.locator( '#billing_address_1' )
		.fill( '123 Test Street' );
	await customerPage.locator( '#billing_city' ).fill( 'Test City' );
	await customerPage.locator( '#billing_postcode' ).fill( '10001' );
	await customerPage.locator( '#billing_state' ).selectOption( 'NY' );
	await customerPage.locator( '#billing_phone' ).fill( '1234567890' );

	await customerPage.getByRole( 'button', { name: /place order/i } ).click();

	// Wait for the order-received page before reading the order ID so the
	// confirmation content is guaranteed to have rendered.
	const orderConfirmationLocator = customerPage
		// Match block or classic confirmation page.
		.locator(
			`
			.woocommerce-order-overview__order strong,
			.wc-block-order-confirmation-summary-list-item:has(.wc-block-order-confirmation-summary-list-item__key:text("Order"))
				.wc-block-order-confirmation-summary-list-item__value
			`
		)
		.first();

	await Promise.race( [
		customerPage.waitForURL( /order-received/i ),
		orderConfirmationLocator.waitFor( { state: 'visible' } ),
	] );

	const orderId = ( await orderConfirmationLocator.textContent() )?.trim();

	expect( orderId ).toMatch( /^\d+$/ );

	await customerContext.close();

	// Assert.
	await admin.visitAdminPage(
		'admin.php',
		`page=wc-orders&action=edit&id=${ orderId }`
	);
	await expect( page.locator( '.wc-clearance-admin-badge' ) ).toBeVisible();
} );
