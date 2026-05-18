import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'can bulk edit products to include in clearance section', async ( {
	page,
	admin,
	requestUtils,
} ) => {
	// Arrange.
	const runId = Date.now();
	const [ product1, product2 ] = await Promise.all( [
		requestUtils.rest( {
			method: 'POST',
			path: '/wc/v3/products',
			data: {
				name: `Bulk Add Clearance Test ${ runId } 1`,
				type: 'simple',
				status: 'publish',
			},
		} ),
		requestUtils.rest( {
			method: 'POST',
			path: '/wc/v3/products',
			data: {
				name: `Bulk Add Clearance Test ${ runId } 2`,
				type: 'simple',
				status: 'publish',
			},
		} ),
	] );

	// Act: navigate to the products list, filter to the test products, and select them.
	await admin.visitAdminPage( 'edit.php', `post_type=product&s=${ runId }` );
	await page.locator( `#cb-select-${ product1.id }` ).check();
	await page.locator( `#cb-select-${ product2.id }` ).check();

	// Choose "Edit" from the bulk actions dropdown and click Apply.
	await page.locator( '#bulk-action-selector-top' ).selectOption( 'edit' );
	await page.locator( '#doaction' ).click();

	// Set the Clearance section field to "Include" and click Update.
	await page
		.locator( 'select[name="wc_outlet_bulk"]' )
		.selectOption( 'yes' );
	await page.locator( '#bulk_edit' ).click();
	await page.waitForLoadState( 'networkidle' );

	// Assert: both products should now be in the clearance section.
	for ( const product of [ product1, product2 ] ) {
		await admin.visitAdminPage(
			'post.php',
			`post=${ product.id }&action=edit`
		);
		await page.getByRole( 'link', { name: 'Inventory' } ).click();
		await expect(
			page.getByRole( 'checkbox', { name: 'Outlet' } )
		).toBeChecked();
	}
} );

test( 'can bulk edit products to remove from clearance section', async ( {
	page,
	admin,
	requestUtils,
} ) => {
	// Arrange: create a product and mark it as clearance.
	const runId = Date.now();
	const product = await requestUtils.rest( {
		method: 'POST',
		path: '/wc/v3/products',
		data: {
			name: `Bulk Remove Clearance Test ${ runId }`,
			type: 'simple',
			status: 'publish',
		},
	} );

	await admin.visitAdminPage(
		'post.php',
		`post=${ product.id }&action=edit`
	);
	await page.getByRole( 'link', { name: 'Inventory' } ).click();
	await page.getByRole( 'checkbox', { name: 'Outlet' } ).check();
	await page.getByRole( 'button', { name: 'Update' } ).click();
	await page.waitForLoadState( 'networkidle' );

	// Act: navigate to the products list, filter to the test product, and select it.
	await admin.visitAdminPage( 'edit.php', `post_type=product&s=${ runId }` );
	await page.locator( `#cb-select-${ product.id }` ).check();

	// Choose "Edit" from the bulk actions dropdown and click Apply.
	await page.locator( '#bulk-action-selector-top' ).selectOption( 'edit' );
	await page.locator( '#doaction' ).click();

	// Set the Clearance section field to "Remove" and click Update.
	await page
		.locator( 'select[name="wc_outlet_bulk"]' )
		.selectOption( 'no' );
	await page.locator( '#bulk_edit' ).click();
	await page.waitForLoadState( 'networkidle' );

	// Assert: the product should no longer be in the clearance section.
	await admin.visitAdminPage(
		'post.php',
		`post=${ product.id }&action=edit`
	);
	await page.getByRole( 'link', { name: 'Inventory' } ).click();
	await expect(
		page.getByRole( 'checkbox', { name: 'Outlet' } )
	).not.toBeChecked();
} );
