import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'can mark a product as outlet using the checkbox', async ( {
	page,
	admin,
	requestUtils,
} ) => {
	// Arrange.
	const product = await requestUtils.rest( {
		method: 'POST',
		path: '/wc/v3/products',
		data: {
			name: 'Test Outlet Product',
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

	await page.getByRole( 'link', { name: 'Inventory' } ).click();

	await expect(
		page.getByRole( 'checkbox', { name: 'Outlet' } )
	).toBeVisible();

	await expect(
		page.getByRole( 'button', { name: 'Update' } )
	).toBeVisible();

	await page.getByRole( 'checkbox', { name: 'Outlet' } ).check();
	await page.getByRole( 'button', { name: 'Update' } ).click();
	await page.waitForLoadState( 'networkidle' );

	// Assert.
	await admin.visitAdminPage(
		'post.php',
		`post=${ product.id }&action=edit`
	);
	await page.getByRole( 'link', { name: 'Inventory' } ).click();
	await expect(
		page.getByRole( 'checkbox', { name: 'Outlet' } )
	).toBeVisible();
	await expect(
		page.getByRole( 'checkbox', { name: 'Outlet' } )
	).toBeChecked();
} );

test( 'can unmark a product as outlet using the checkbox', async ( {
	page,
	admin,
	requestUtils,
} ) => {
	// Arrange.
	const product = await requestUtils.rest( {
		method: 'POST',
		path: '/wc/v3/products',
		data: {
			name: 'Test Outlet Product',
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
	await page.getByRole( 'link', { name: 'Inventory' } ).click();
	await page.getByRole( 'checkbox', { name: 'Outlet' } ).check();
	await page.getByRole( 'button', { name: 'Update' } ).click();
	await page.waitForLoadState( 'networkidle' );
	await page.getByRole( 'link', { name: 'Inventory' } ).click();
	await page.getByRole( 'checkbox', { name: 'Outlet' } ).uncheck();
	await page.getByRole( 'button', { name: 'Update' } ).click();
	await page.waitForLoadState( 'networkidle' );

	// Assert.
	await page.getByRole( 'link', { name: 'Inventory' } ).click();
	await expect(
		page.getByRole( 'checkbox', { name: 'Outlet' } )
	).toBeVisible();
	await expect(
		page.getByRole( 'checkbox', { name: 'Outlet' } )
	).not.toBeChecked();
} );

test( 'can toggle the outlet checkbox by clicking its label text', async ( {
	page,
	admin,
	requestUtils,
} ) => {
	// Arrange.
	const product = await requestUtils.rest( {
		method: 'POST',
		path: '/wc/v3/products',
		data: {
			name: 'Test Outlet Product',
			type: 'simple',
			status: 'publish',
		},
	} );

	await admin.visitAdminPage(
		'post.php',
		`post=${ product.id }&action=edit`
	);

	await page.getByRole( 'link', { name: 'Inventory' } ).click();

	const checkbox = page.getByRole( 'checkbox', {
		name: 'Outlet',
	} );
	await expect( checkbox ).not.toBeChecked();

	// Act - click the label text beside the checkbox rather than the checkbox itself.
	await page.getByText( 'Include in outlet' ).click();

	// Assert.
	await expect( checkbox ).toBeChecked();

	// Act - click the label text again to toggle the checkbox off.
	await page.getByText( 'Include in outlet' ).click();

	// Assert.
	await expect( checkbox ).not.toBeChecked();
} );

test( 'outlet panel has correct styles', async ( {
	page,
	admin,
	requestUtils,
} ) => {
	// Arrange.
	const product = await requestUtils.rest( {
		method: 'POST',
		path: '/wc/v3/products',
		data: {
			name: 'Style Test Product',
			type: 'simple',
			status: 'publish',
		},
	} );

	// Act.
	await admin.visitAdminPage(
		'post.php',
		`post=${ product.id }&action=edit`
	);
	await page.getByRole( 'link', { name: 'Inventory' } ).click();

	// Assert: help text has font-size 12px.
	const helpText = page.locator( '.outletpro-status-help' );
	await expect( helpText ).toBeVisible();
	await expect( helpText ).toHaveCSS( 'font-size', '12px' );

	// Assert: panel has margin-bottom of 1.5em (verified against the element's own font-size).
	const panel = page.locator( '.outletpro-status-panel' );
	await expect( panel ).toBeVisible();
	const panelFontSizePx = await panel.evaluate( ( el ) =>
		parseFloat( window.getComputedStyle( el ).fontSize )
	);
	await expect( panel ).toHaveCSS(
		'margin-bottom',
		`${ panelFontSizePx * 1.5 }px`
	);
} );
