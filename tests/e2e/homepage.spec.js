/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'site homepage is accessible', async ( { page } ) => {
	await page.goto( '/' );
	await expect( page ).toHaveTitle( /.+/ );
} );
