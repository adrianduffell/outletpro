/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { test } from '@wordpress/e2e-test-utils-playwright';

test( 'set store to live mode', async ( { page, admin } ) => {
	await admin.visitAdminPage(
		'admin.php',
		'page=wc-settings&tab=site-visibility'
	);

	const comingSoonRadio = page.getByRole( 'radio', { name: 'Coming soon' } );
	if ( await comingSoonRadio.isChecked() ) {
		await page.getByRole( 'radio', { name: 'Live' } ).check();
		await page.getByRole( 'button', { name: 'Save changes' } ).click();
		await page.getByText( 'Your settings have been saved.' ).waitFor();
	}
} );
