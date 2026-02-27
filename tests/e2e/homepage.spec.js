const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

test( 'site homepage is accessible', async ( { page } ) => {
	await page.goto( '/' );
	await expect( page ).toHaveTitle( /.+/ );
} );
