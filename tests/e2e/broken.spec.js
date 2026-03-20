import { test, expect } from '@wordpress/e2e-test-utils-playwright';

// This test is deliberately broken to verify that CI captures and uploads
// Playwright artifacts (screenshots, traces) from failed test runs.
test( 'verifies artifact capture on test failure', async ( {
	page,
} ) => {
	await page.goto( '/' );

	// Assert an impossible title so the test always fails.
	await expect( page ).toHaveTitle( 'This title will never match' );
} );
