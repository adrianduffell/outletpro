import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'skip core profiler', async ( { page, admin } ) => {
	// Arrange.
	await admin.visitAdminPage(
		'admin.php',
		'page=wc-admin&path=/setup-wizard'
	);

	// Act.
	await page
		.getByRole( 'checkbox', { name: /share my data/i } )
		.uncheck();

	await page.getByRole( 'button', { name: /skip guided setup/i } ).click();

	await page
		.getByRole( 'combobox', { name: /where is your store based/i } )
		.selectOption( 'US:CA' );

	await page.getByRole( 'button', { name: /continue/i } ).click();

	await page.waitForLoadState( 'networkidle' );

	// Assert.
	await expect( page ).not.toHaveURL( /setup-wizard/ );
} );
