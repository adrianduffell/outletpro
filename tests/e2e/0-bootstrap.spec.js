import { test } from '@wordpress/e2e-test-utils-playwright';

test( 'set store to live mode', async ( { page, admin } ) => {
	// Arrange.
	await admin.visitAdminPage(
		'admin.php',
		'page=wc-settings&tab=site-visibility'
	);

	// Act: switch to Live if the store is currently in Coming soon mode.
	const comingSoonRadio = page.getByRole( 'radio', { name: 'Coming soon' } );
	if ( await comingSoonRadio.isChecked() ) {
		await page.getByRole( 'radio', { name: 'Live' } ).check();
		await page.getByRole( 'button', { name: 'Save changes' } ).click();
		await page.waitForLoadState( 'networkidle' );
	}
} );
