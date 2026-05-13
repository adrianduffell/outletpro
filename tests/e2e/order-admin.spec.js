import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'admin sees clearance badge on order item', async ( {
	page,
	admin,
	requestUtils,
} ) => {
	// Arrange.
	const runId = Date.now();
	const product = await requestUtils.rest( {
		method: 'POST',
		path: '/wc/v3/products',
		data: {
			name: `Admin Order Badge Test Product ${ runId }`,
			type: 'simple',
			status: 'publish',
			regular_price: '9.99',
		},
	} );

	const order = await requestUtils.rest( {
		method: 'POST',
		path: '/wc/v3/orders',
		data: {
			status: 'processing',
			billing: {
				first_name: 'Test',
				last_name: 'Customer',
				email: `order-admin-${ runId }@example.com`,
			},
			line_items: [
				{
					product_id: product.id,
					quantity: 1,
					meta_data: [ { key: '_wc_clearance', value: 'yes' } ],
				},
			],
		},
	} );

	// Assert.
	await admin.visitAdminPage(
		'admin.php',
		`page=wc-orders&action=edit&id=${ order.id }`
	);
	await expect(
		page.locator( '.wc-clearance-admin-badge' ).first()
	).toBeVisible();
} );
