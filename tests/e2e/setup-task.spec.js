import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'include products in clearance section setup task', async ( {
	page,
	admin,
	requestUtils,
} ) => {
	// Arrange.
	const product = await requestUtils.rest( {
		method: 'POST',
		path: '/wc/v3/products',
		data: {
			name: 'Test Clearance Product',
			type: 'simple',
			status: 'publish',
		},
	} );

	await admin.visitAdminPage( 'admin.php', 'page=wc-admin' );
	const taskItem = page.locator( '.woocommerce-task-list__item', {
		hasText: 'Include products in the clearance section',
	} );
	await expect( taskItem ).toBeVisible();
	await expect( taskItem ).not.toHaveClass( /is-complete/ );

	await taskItem.click();
	await expect( page ).toHaveURL( /edit\.php\?post_type=product/ );

	// Act.
	await admin.visitAdminPage(
		'post.php',
		`post=${ product.id }&action=edit`
	);
	await page.getByRole( 'link', { name: 'Inventory' } ).click();
	await page.getByRole( 'checkbox', { name: 'Clearance section' } ).check();
	await page.getByRole( 'button', { name: 'Update' } ).click();
	await page.waitForLoadState( 'networkidle' );

	// Assert.
	await admin.visitAdminPage( 'admin.php', 'page=wc-admin' );
	await expect( taskItem ).toHaveClass( /complete/ );
} );
