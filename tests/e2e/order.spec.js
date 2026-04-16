import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'customer places clearance order and admin sees clearance badge on order', async ( {
	page,
	admin,
	requestUtils,
	browser,
} ) => {
	// Arrange.
	await requestUtils.rest( {
		method: 'PUT',
		path: '/wc/v3/payment_gateways/cod',
		data: { enabled: true },
	} );

	const product = await requestUtils.rest( {
		method: 'POST',
		path: '/wc/v3/products',
		data: {
			name: 'Order Flow Test Product',
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

	const cartUpdatePromise = customerPage
		.locator( '.added_to_cart' )
		.first()
		.waitFor( { state: 'attached' } );

	await customerPage
		.getByRole( 'button', { name: /add to cart/i } )
		.first()
		.click();

	await cartUpdatePromise;

	await customerPage.getByRole('link', { name: /checkout/i }).first().click();

	await customerPage.locator( '#billing_email' ).fill( 'test@example.com' );
	await customerPage.locator( '#billing_first_name' ).fill( 'Test' );
	await customerPage.locator( '#billing_last_name' ).fill( 'Customer' );
	await customerPage.locator( '#billing_country' ).selectOption( 'US' );
	await customerPage.locator( '#billing_address_1' ).fill( '123 Test Street' );
	await customerPage.locator( '#billing_city' ).fill( 'Test City' );
	await customerPage.locator( '#billing_postcode' ).fill( '10001' );
	await customerPage.locator( '#billing_state' ).selectOption( 'NY' );
	await customerPage.locator( '#billing_phone' ).fill( '1234567890' );

	await customerPage.getByRole( 'button', { name: /place order/i } ).click();

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
