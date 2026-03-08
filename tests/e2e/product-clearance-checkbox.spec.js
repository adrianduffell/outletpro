import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'can mark a product as clearance using the checkbox', async ( {
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

	// Act.
	await admin.visitAdminPage( 'post.php', `post=${ product.id }&action=edit` );
	await page.getByLabel( 'Include in clearance section' ).check();
	await page.getByRole( 'button', { name: 'Update' } ).click();
	await page.waitForLoadState( 'networkidle' );

	// Assert.
	await admin.visitAdminPage( 'post.php', `post=${ product.id }&action=edit` );
	await expect(
		page.getByLabel( 'Include in clearance section' )
	).toBeChecked();

	// Cleanup.
	await requestUtils.rest( {
		method: 'DELETE',
		path: `/wc/v3/products/${ product.id }`,
		data: { force: true },
	} );
} );

test( 'can unmark a product as clearance using the checkbox', async ( {
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
	await admin.visitAdminPage( 'post.php', `post=${ product.id }&action=edit` );
	await page.getByLabel( 'Include in clearance section' ).check();
	await page.getByRole( 'button', { name: 'Update' } ).click();
	await page.waitForLoadState( 'networkidle' );

	// Act.
	await admin.visitAdminPage( 'post.php', `post=${ product.id }&action=edit` );
	await page.getByLabel( 'Include in clearance section' ).uncheck();
	await page.getByRole( 'button', { name: 'Update' } ).click();
	await page.waitForLoadState( 'networkidle' );

	// Assert.
	await admin.visitAdminPage( 'post.php', `post=${ product.id }&action=edit` );
	await expect(
		page.getByLabel( 'Include in clearance section' )
	).not.toBeChecked();

	// Cleanup.
	await requestUtils.rest( {
		method: 'DELETE',
		path: `/wc/v3/products/${ product.id }`,
		data: { force: true },
	} );
} );
