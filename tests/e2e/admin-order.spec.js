import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import {
	addClearanceProductToCart,
	createClearanceOrderProduct,
	createCustomerPage,
	placeOrder,
} from './order-flow.js';

test( 'admin sees clearance badge on order', async ( {
	page,
	admin,
	requestUtils,
	browser,
} ) => {
	// Arrange.
	const { clearancePage } = await createClearanceOrderProduct( {
		page,
		admin,
		requestUtils,
	} );
	const { customerContext, customerPage } =
		await createCustomerPage( browser );

	await addClearanceProductToCart( { customerPage, clearancePage } );

	// Act.
	const orderId = await placeOrder( customerPage );

	await customerContext.close();

	// Assert.
	await admin.visitAdminPage(
		'admin.php',
		`page=wc-orders&action=edit&id=${ orderId }`
	);
	await expect(
		page.locator( '.wc-clearance-admin-badge' ).first()
	).toBeVisible();
} );
