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
	await admin.visitAdminPage(
		'post.php',
		`post=${ product.id }&action=edit`
	);

	await expect(
		page.getByRole( 'textbox', {
			name: 'Regular price ($)',
		} )
	).toBeVisible();

	await expect(
		page.getByRole( 'checkbox', { name: 'Clearance section' } )
	).toBeVisible();

	await expect(
		page.getByRole( 'button', { name: 'Update' } )
	).toBeVisible();

	await page.getByRole( 'checkbox', { name: 'Clearance section' } ).check();
	await page.getByRole( 'button', { name: 'Update' } ).click();
	await page.waitForLoadState( 'networkidle' );

	// Assert.
	await admin.visitAdminPage(
		'post.php',
		`post=${ product.id }&action=edit`
	);
	await expect(
		page.getByRole( 'checkbox', { name: 'Clearance section' } )
	).toBeVisible();
	await expect(
		page.getByRole( 'checkbox', { name: 'Clearance section' } )
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
	await admin.visitAdminPage(
		'post.php',
		`post=${ product.id }&action=edit`
	);

	await page.getByRole( 'button', { name: 'Update' } ).click();
	await page.waitForLoadState( 'networkidle' );

	// Act.
	await admin.visitAdminPage(
		'post.php',
		`post=${ product.id }&action=edit`
	);
	await page.getByRole( 'checkbox', { name: 'Clearance section' } ).check();
	await page.getByRole( 'button', { name: 'Update' } ).click();
	await page.waitForLoadState( 'networkidle' );
	await page.getByRole( 'checkbox', { name: 'Clearance section' } ).uncheck();
	await page.getByRole( 'button', { name: 'Update' } ).click();
	await page.waitForLoadState( 'networkidle' );

	// Assert.
	await expect(
		page.getByRole( 'checkbox', { name: 'Clearance section' } )
	).toBeVisible();
	await expect(
		page.getByRole( 'checkbox', { name: 'Clearance section' } )
	).not.toBeChecked();
} );

test( 'can toggle the clearance checkbox by clicking its label text', async ( {
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

	await admin.visitAdminPage(
		'post.php',
		`post=${ product.id }&action=edit`
	);

	const checkbox = page.getByRole( 'checkbox', {
		name: 'Clearance section',
	} );
	await expect( checkbox ).not.toBeChecked();

	// Act - click the label text beside the checkbox rather than the checkbox itself.
	await page.getByText( 'Include in clearance section' ).click();

	// Assert.
	await expect( checkbox ).toBeChecked();

	// Act - click the label text again to toggle the checkbox off.
	await page.getByText( 'Include in clearance section' ).click();

	// Assert.
	await expect( checkbox ).not.toBeChecked();
} );
