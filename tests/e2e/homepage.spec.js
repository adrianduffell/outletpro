import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'site homepage is accessible', async ( { page, admin } ) => {
	// Arrange: ensure the store is set to Live (not Coming soon).
	await admin.visitAdminPage(
		'admin.php',
		'page=wc-settings&tab=site-visibility'
	);
	const comingSoonRadio = page.getByRole( 'radio', {
		name: 'Coming soon',
	} );
	if ( await comingSoonRadio.isChecked() ) {
		await page.getByRole( 'radio', { name: 'Live' } ).check();
		await page.getByRole( 'button', { name: 'Save changes' } ).click();
		await page.waitForLoadState( 'networkidle' );
	}

	// Act.
	await page.goto( '/' );

	// Assert.
	await expect( page ).toHaveTitle( /.+/ );
} );
