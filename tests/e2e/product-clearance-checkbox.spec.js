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
	console.log( 'Created product with ID:', product.id );
	// Act.
	await admin.visitAdminPage( 'post.php', `post=${ product.id }&action=edit` );
	console.log( 'navigation to product edit page complete' );

	await expect(
		page.getByRole('textbox', { name: 'Regular price ($)' })
	).toBeVisible();

	console.log( 'regular price field visible' );
	// Check section heading is visible.
	await expect(
		page.getByRole('checkbox', { name: 'Clearance section' })
	).toBeVisible();
	await expect(
		page.getByRole('button', { name: 'Update' })
	).toBeVisible();

console.log( 'checkbox visible' );
	page.getByRole('checkbox', { name: 'Clearance section' }).check();



	console.log( 'checkbox checked' );
	await page.getByRole( 'button', { name: 'Update' } ).click();
	console.log( 'update button clicked' );
	await page.waitForLoadState( 'networkidle' );

	// Assert.
	await admin.visitAdminPage( 'post.php', `post=${ product.id }&action=edit` );
	await expect(
		page.getByRole('checkbox', { name: 'Clearance section' })
	).toBeVisible();
	await expect(
		page.getByRole('checkbox', { name: 'Clearance section' })
	).toBeChecked();
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

	await page.getByRole( 'button', { name: 'Update' } ).click();
	await page.waitForLoadState( 'networkidle' );

	// Act.
	await admin.visitAdminPage( 'post.php', `post=${ product.id }&action=edit` );
	await page.getByRole('checkbox', { name: 'Clearance section' }).check();
	await page.getByRole( 'button', { name: 'Update' } ).click();
	await page.waitForLoadState( 'networkidle' );
	await page.getByRole('checkbox', { name: 'Clearance section' }).uncheck();
	await page.getByRole( 'button', { name: 'Update' } ).click();
	await page.waitForLoadState( 'networkidle' );

	// Assert.
	await expect(
		page.getByRole('checkbox', { name: 'Clearance section' })
	).toBeVisible();
	await expect(
		page.getByRole('checkbox', { name: 'Clearance section' })
	).not.toBeChecked();
} );
