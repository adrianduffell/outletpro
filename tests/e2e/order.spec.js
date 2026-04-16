import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'customer places clearance order and admin sees clearance badge on order', async ( {
	page,
	admin,
	requestUtils,
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
	await page.waitForLoadState( 'networkidle' );

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

	// Act.
	await page.goto( '/wp-login.php?action=logout' );
	await page.getByRole( 'link', { name: /log out/i } ).click();
	await page.waitForURL( '**/wp-login.php**' );

	await page.goto( clearancePage.link );

	const cartUpdatePromise = page.waitForResponse(
		( response ) =>
			response.url().includes( '/wc/store/v1/cart/add-item' ) &&
			response.ok()
	);
	await page
		.getByRole( 'button', { name: /add to cart/i } )
		.first()
		.click();
	await cartUpdatePromise;

	await page.goto( '/checkout/' );

	await page.locator( '#email' ).fill( 'test@example.com' );
	await page.locator( '#billing-first_name' ).fill( 'Test' );
	await page.locator( '#billing-last_name' ).fill( 'Customer' );
	await page.locator( '#billing-address_1' ).fill( '123 Test Street' );
	await page.locator( '#billing-city' ).fill( 'Test City' );
	await page.locator( '#billing-postcode' ).fill( '10001' );
	await page.locator( '#billing-state' ).selectOption( 'NY' );

	await page.getByLabel( 'Cash on delivery' ).click();

	await page.getByRole( 'button', { name: /place order/i } ).click();
	await page.waitForURL( '**/order-received/**' );

	const orderMatch = page.url().match( /order-received\/(\d+)/ );
	expect( orderMatch ).not.toBeNull();
	const orderId = orderMatch[ 1 ];

	// Assert.
	await admin.visitAdminPage(
		'admin.php',
		`page=wc-orders&action=edit&id=${ orderId }`
	);
	await expect( page.locator( '.wc-clearance-admin-badge' ) ).toBeVisible();
} );
